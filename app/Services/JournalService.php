<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JournalService
{
    public function createJournal(array $data, string $action = 'draft'): JournalEntry
    {
        return DB::transaction(function () use ($data, $action) {
            // Validate balance if posting
            if ($action === 'post') {
                $this->validateBalance($data['lines']);
            }

            // Get fiscal period
            $fiscalPeriod = $this->getFiscalPeriod($data['journal_date']);

            // Generate journal number
            $journalNumber = $this->generateJournalNumber($data['journal_date']);

            // Calculate totals
            $totalDebit = collect($data['lines'])->sum('debit_amount');
            $totalCredit = collect($data['lines'])->sum('credit_amount');

            // Create journal entry
            $journal = JournalEntry::create([
                'journal_number' => $journalNumber,
                'journal_date' => $data['journal_date'],
                'reference_number' => $data['reference_number'] ?? null,
                'description' => $data['description'],
                'entry_type' => $data['entry_type'] ?? 'manual',
                'status' => $action === 'post' ? 'posted' : 'draft',
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'fiscal_period_id' => $fiscalPeriod->id,
                'branch_id' => auth()->user()->branch_id,
                'posted_at' => $action === 'post' ? now() : null,
                'posted_by' => $action === 'post' ? auth()->id() : null,
                'created_by' => auth()->id(),
            ]);

            // Create lines
            foreach ($data['lines'] as $index => $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? '',
                    'debit_amount' => $line['debit_amount'] ?? 0,
                    'credit_amount' => $line['credit_amount'] ?? 0,
                    'line_number' => $index + 1,
                ]);
            }

            // Update account balances if posting
            if ($action === 'post') {
                $journal->load('lines.account');
                foreach ($journal->lines as $line) {
                    $this->updateAccountBalance($line);
                }
            }

            return $journal;
        });
    }

    public function generateJournalNumber(string $date): string
    {
        $date = Carbon::parse($date);
        $prefix = 'JE-' . $date->format('Ym') . '-';
        
        $lastNumber = JournalEntry::where('journal_number', 'like', $prefix . '%')
            ->orderBy('journal_number', 'desc')
            ->value('journal_number');
        
        $nextNumber = $lastNumber ? intval(substr($lastNumber, -4)) + 1 : 1;
        
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function validateBalance(array $lines): void
    {
        $totalDebit = collect($lines)->sum('debit_amount');
        $totalCredit = collect($lines)->sum('credit_amount');

        if ($totalDebit != $totalCredit) {
            throw new \Exception('Journal entry is not balanced. Total debit must equal total credit.');
        }

        if ($totalDebit == 0) {
            throw new \Exception('Journal entry cannot have zero amounts.');
        }
    }

    public function getFiscalPeriod(string $date): FiscalPeriod
    {
        $period = FiscalPeriod::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('status', 'open')
            ->first();

        if (!$period) {
            throw new \Exception('No open fiscal period found for date: ' . $date);
        }

        return $period;
    }

    public function updateAccountBalance(JournalEntryLine $line): void
    {
        $account = $line->account;
        $debit = $line->debit_amount;
        $credit = $line->credit_amount;

        // Normal balance logic
        $isDebitAccount = in_array($account->type, ['asset', 'expense']);

        if ($debit > 0) {
            $account->balance += $isDebitAccount ? $debit : -$debit;
        }

        if ($credit > 0) {
            $account->balance += $isDebitAccount ? -$credit : $credit;
        }

        $account->save();
    }

    public function postJournal(int $journalId): void
    {
        DB::transaction(function () use ($journalId) {
            $journal = JournalEntry::with('lines.account')->findOrFail($journalId);

            // Validate
            if ($journal->status !== 'draft') {
                throw new \Exception('Only draft entries can be posted');
            }

            if ($journal->total_debit != $journal->total_credit) {
                throw new \Exception('Entry is not balanced');
            }

            // Check fiscal period
            $period = FiscalPeriod::find($journal->fiscal_period_id);
            if ($period->status !== 'open') {
                throw new \Exception("Cannot post to closed period {$period->name}");
            }

            // Update account balances
            foreach ($journal->lines as $line) {
                $this->updateAccountBalance($line);
            }

            // Update journal status
            $journal->update([
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ]);
        });
    }

    public function reverseJournal(int $journalId, ?string $reversalDate = null): JournalEntry
    {
        return DB::transaction(function () use ($journalId, $reversalDate) {
            $original = JournalEntry::with('lines')->findOrFail($journalId);

            // Validate
            if ($original->status !== 'posted') {
                throw new \Exception('Only posted entries can be reversed');
            }

            if ($original->reversed_journal_id !== null) {
                throw new \Exception('Entry already reversed');
            }

            $reversalDate = $reversalDate ?? now()->toDateString();

            // Create reversal entry
            $reversal = JournalEntry::create([
                'journal_number' => $this->generateJournalNumber($reversalDate),
                'journal_date' => $reversalDate,
                'reference_number' => "Reversal of {$original->journal_number}",
                'description' => "REVERSAL: {$original->description}",
                'entry_type' => 'adjustment',
                'status' => 'posted',
                'total_debit' => $original->total_debit,
                'total_credit' => $original->total_credit,
                'fiscal_period_id' => $this->getFiscalPeriod($reversalDate)->id,
                'branch_id' => $original->branch_id,
                'posted_at' => now(),
                'posted_by' => auth()->id(),
                'created_by' => auth()->id(),
            ]);

            // Create reversal lines (swap debit/credit)
            foreach ($original->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversal->id,
                    'account_id' => $line->account_id,
                    'description' => $line->description,
                    'debit_amount' => $line->credit_amount,  // swap
                    'credit_amount' => $line->debit_amount,  // swap
                    'line_number' => $line->line_number,
                ]);
            }

            // Update account balances
            $reversal->load('lines.account');
            foreach ($reversal->lines as $line) {
                $this->updateAccountBalance($line);
            }

            // Update original entry
            $original->update([
                'status' => 'reversed',
                'reversed_journal_id' => $reversal->id,
            ]);

            return $reversal;
        });
    }
}
