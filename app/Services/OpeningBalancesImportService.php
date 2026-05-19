<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OpeningBalancesImportService
{
    public function preview(array $payload): array
    {
        $startedAt = microtime(true);
        $rows = $this->parse($payload);
        $mapped = $this->mapRows($rows, $payload);
        $validated = $this->validateRows($mapped);

        return $this->summary($validated, $startedAt, false);
    }

    public function import(array $payload): array
    {
        $startedAt = microtime(true);
        $rows = $this->parse($payload);
        $mapped = $this->mapRows($rows, $payload);
        $validated = $this->validateRows($mapped);
        $summary = $this->summary($validated, $startedAt, false);

        if ($summary['skipped'] > 0) {
            $summary['executed'] = false;
            $summary['rejected'] = true;
            $summary['message'] = 'Opening balance memiliki row invalid.';

            return $summary;
        }

        if (bccomp((string) $summary['total_debit'], (string) $summary['total_credit'], 2) !== 0) {
            $summary['executed'] = false;
            $summary['rejected'] = true;
            $summary['message'] = 'Opening balance tidak balance.';

            return $summary;
        }

        $journalEntry = null;

        DB::transaction(function () use ($validated, $summary, &$journalEntry) {
            $journalEntry = JournalEntry::create([
                'journal_number' => $this->journalNumber(),
                'journal_date' => $summary['balance_date'],
                'fiscal_period_id' => $summary['fiscal_period_id'],
                'type' => 'manual',
                'reference_type' => 'opening_balance',
                'reference_id' => null,
                'description' => 'Opening balance import',
                'total_debit' => $summary['total_debit'],
                'total_credit' => $summary['total_credit'],
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            foreach ($validated as $row) {
                $journalEntry->lines()->create([
                    'account_id' => $row['data']['account_id'],
                    'description' => $row['data']['description'],
                    'debit' => $row['data']['debit'],
                    'credit' => $row['data']['credit'],
                ]);
            }
        });

        $result = $this->summary($validated, $startedAt, true);
        $result['journal_entry_id'] = $journalEntry?->id;
        $result['journal_number'] = $journalEntry?->journal_number;
        $result['imported'] = $journalEntry ? count($validated) : 0;

        return $result;
    }

    private function parse(array $payload): array
    {
        $rows = $payload['opening_balances'] ?? $payload['balances'] ?? [];

        if (($payload['data'] ?? null) && is_array($payload['data'])) {
            $rows = $payload['data']['opening_balances'] ?? $payload['data']['balances'] ?? $rows;
        }

        if (($payload['rows'] ?? null) && is_array($payload['rows'])) {
            $rows = $payload['rows']['opening_balances'] ?? $payload['rows']['balances'] ?? $rows;
        }

        if (!is_array($rows)) {
            throw new InvalidArgumentException('Payload opening balances harus berupa array.');
        }

        if (count($rows) === 0) {
            throw new InvalidArgumentException('Payload opening balances kosong.');
        }

        return $rows;
    }

    private function mapRows(array $rows, array $payload): array
    {
        $result = [];
        $fiscalPeriodId = $this->intValue($payload, ['fiscal_period_id', 'period_id'], 0);
        $balanceDate = $this->stringValue($payload, ['balance_date', 'opening_date', 'date']);

        foreach ($rows as $index => $row) {
            $accountCode = $this->nullableStringValue($row, ['account_code', 'code', 'kode_akun']);
            $accountId = $this->intValue($row, ['account_id'], 0);
            $debit = $this->nullableFloatValue($row, ['debit']);
            $credit = $this->nullableFloatValue($row, ['credit']);
            $amount = $this->nullableFloatValue($row, ['amount', 'balance', 'saldo']);

            $result[] = [
                'row_number' => $index + 1,
                'source' => $row,
                'data' => [
                    'account_code' => $accountCode,
                    'account_id' => $accountId,
                    'debit' => $debit,
                    'credit' => $credit,
                    'amount' => $amount,
                    'normal_balance' => $this->nullableStringValue($row, ['normal_balance', 'saldo_normal']),
                    'description' => $this->nullableStringValue($row, ['description', 'keterangan']) ?? 'Opening balance import',
                    'fiscal_period_id' => $this->intValue($row, ['fiscal_period_id', 'period_id'], $fiscalPeriodId),
                    'balance_date' => $this->stringValue($row, ['balance_date', 'opening_date', 'date'], $balanceDate),
                ],
                'valid' => true,
                'errors' => [],
            ];
        }

        return $result;
    }

    private function validateRows(array $rows): array
    {
        $accountsByCode = Account::query()->get()->keyBy('code');
        $accountsById = Account::query()->get()->keyBy('id');
        $periods = FiscalPeriod::query()->pluck('id')->flip();

        foreach ($rows as $index => $row) {
            $data = $row['data'];
            $errors = [];
            $account = null;

            if ($data['account_id']) {
                $account = $accountsById->get($data['account_id']);
            } elseif ($data['account_code']) {
                $account = $accountsByCode->get($data['account_code']);
            }

            if (!$account) {
                $errors[] = 'Akun tidak ditemukan.';
            } else {
                if (!$account->is_active) {
                    $errors[] = 'Akun tidak aktif.';
                }

                if ($account->is_header) {
                    $errors[] = 'Akun header tidak boleh dipakai journal line.';
                }

                $rows[$index]['data']['account_id'] = $account->id;
                $rows[$index]['data']['account_code'] = $account->code;
                $rows[$index]['data']['account_name'] = $account->name;
                $rows[$index]['data']['account_type'] = $account->type;
            }

            if (!$data['fiscal_period_id'] || !$periods->has($data['fiscal_period_id'])) {
                $errors[] = 'Fiscal period tidak valid.';
            }

            if (!$data['balance_date'] || strtotime($data['balance_date']) === false) {
                $errors[] = 'Tanggal saldo awal tidak valid.';
            }

            $debit = $data['debit'];
            $credit = $data['credit'];

            if ($debit === null && $credit === null) {
                [$debit, $credit] = $this->amountToDebitCredit($data['amount'], $account, $data['normal_balance']);
            }

            $debit = round((float) ($debit ?? 0), 2);
            $credit = round((float) ($credit ?? 0), 2);

            if ($debit < 0 || $credit < 0) {
                $errors[] = 'Debit/credit tidak boleh negatif.';
            }

            if ($debit == 0.0 && $credit == 0.0) {
                $errors[] = 'Debit atau credit wajib lebih dari 0.';
            }

            if ($debit > 0 && $credit > 0) {
                $errors[] = 'Satu row tidak boleh punya debit dan credit sekaligus.';
            }

            $rows[$index]['data']['debit'] = $debit;
            $rows[$index]['data']['credit'] = $credit;
            $rows[$index]['valid'] = count($errors) === 0;
            $rows[$index]['errors'] = $errors;
        }

        return $rows;
    }

    private function summary(array $rows, float $startedAt, bool $executed): array
    {
        $validRows = array_values(array_filter($rows, fn (array $row) => $row['valid']));
        $errors = array_values(array_map(
            fn (array $row) => [
                'row_number' => $row['row_number'],
                'account_code' => $row['data']['account_code'],
                'errors' => $row['errors'],
            ],
            array_filter($rows, fn (array $row) => !$row['valid'])
        ));
        $totalDebit = round(array_sum(array_map(fn (array $row) => $row['data']['debit'], $validRows)), 2);
        $totalCredit = round(array_sum(array_map(fn (array $row) => $row['data']['credit'], $validRows)), 2);
        $first = $rows[0]['data'] ?? [];

        return [
            'executed' => $executed,
            'total' => count($rows),
            'valid' => count($validRows),
            'imported' => $executed ? count($validRows) : 0,
            'skipped' => count($rows) - count($validRows),
            'errors' => $errors,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'difference' => round($totalDebit - $totalCredit, 2),
            'fiscal_period_id' => $first['fiscal_period_id'] ?? null,
            'balance_date' => $first['balance_date'] ?? null,
            'accounts' => array_values(array_map(fn (array $row) => $row['data'], $rows)),
            'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
        ];
    }

    private function amountToDebitCredit(?float $amount, ?Account $account, ?string $normalBalance): array
    {
        if ($amount === null || !$account) {
            return [0.0, 0.0];
        }

        $balanceSide = $normalBalance ?: $account->getNormalBalance();
        $absolute = abs($amount);
        $isDebit = $balanceSide === 'debit';

        if ($amount < 0) {
            $isDebit = !$isDebit;
        }

        return $isDebit ? [$absolute, 0.0] : [0.0, $absolute];
    }

    private function journalNumber(): string
    {
        $prefix = 'OB-'.now()->format('Ymd').'-';
        $last = JournalEntry::where('journal_number', 'like', $prefix.'%')
            ->orderByDesc('journal_number')
            ->first();
        $next = $last ? ((int) substr($last->journal_number, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function stringValue(array $row, array $keys, ?string $default = ''): string
    {
        return (string) ($this->value($row, $keys) ?? $default);
    }

    private function nullableStringValue(array $row, array $keys): ?string
    {
        $value = $this->value($row, $keys);

        return $value === null || $value === '' ? null : (string) $value;
    }

    private function intValue(array $row, array $keys, int $default): int
    {
        $value = $this->value($row, $keys);

        return $value === null || $value === '' ? $default : (int) $value;
    }

    private function nullableFloatValue(array $row, array $keys): ?float
    {
        $value = $this->value($row, $keys);

        return $value === null || $value === '' ? null : (float) $value;
    }

    private function value(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        return null;
    }
}
