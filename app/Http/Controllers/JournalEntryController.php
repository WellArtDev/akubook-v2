<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Requests\UpdateJournalEntryRequest;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JournalEntryController extends Controller
{
    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index(Request $request)
    {
        $query = JournalEntry::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('journal_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('journal_date', '<=', $request->end_date);
        }

        // Search by journal_number or description
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('journal_number', 'ilike', '%' . $request->search . '%')
                  ->orWhere('description', 'ilike', '%' . $request->search . '%');
            });
        }

        $journals = $query->orderBy('journal_date', 'desc')
            ->orderBy('journal_number', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('JournalEntries/Index', [
            'journals' => $journals,
            'filters' => $request->only(['status', 'start_date', 'end_date', 'search']),
        ]);
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        return Inertia::render('JournalEntries/Create', [
            'accounts' => $accounts,
        ]);
    }

    public function store(StoreJournalEntryRequest $request)
    {
        try {
            $action = $request->input('action', 'draft'); // 'draft' or 'post'
            
            $journal = $this->journalService->createJournal(
                $request->validated(),
                $action
            );

            $message = $action === 'post' 
                ? "Journal entry {$journal->journal_number} berhasil dibuat dan diposting."
                : "Journal entry {$journal->journal_number} berhasil disimpan sebagai draft.";

            return redirect()->route('journal-entries.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load(['lines.account', 'fiscalPeriod', 'postedBy', 'reversedJournal']);

        return Inertia::render('JournalEntries/Show', [
            'journal' => $journalEntry,
        ]);
    }

    public function edit(JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'draft') {
            return back()->withErrors(['error' => 'Hanya draft entry yang dapat diedit.']);
        }

        $journalEntry->load('lines.account');

        $accounts = Account::where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        return Inertia::render('JournalEntries/Edit', [
            'journal' => $journalEntry,
            'accounts' => $accounts,
        ]);
    }

    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'draft') {
            return back()->withErrors(['error' => 'Hanya draft entry yang dapat diedit.']);
        }

        try {
            // Delete existing lines
            $journalEntry->lines()->delete();

            // Update journal entry
            $action = $request->input('action', 'draft');
            
            // Recreate journal (simpler than updating)
            $journalEntry->delete();
            
            $journal = $this->journalService->createJournal(
                $request->validated(),
                $action
            );

            $message = $action === 'post' 
                ? "Journal entry {$journal->journal_number} berhasil diperbarui dan diposting."
                : "Journal entry {$journal->journal_number} berhasil diperbarui.";

            return redirect()->route('journal-entries.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(JournalEntry $journalEntry)
    {
        if ($journalEntry->status !== 'draft') {
            return back()->withErrors(['error' => 'Hanya draft entry yang dapat dihapus.']);
        }

        $journalEntry->delete();

        return redirect()->route('journal-entries.index')
            ->with('success', "Journal entry {$journalEntry->journal_number} berhasil dihapus.");
    }

    public function post(JournalEntry $journalEntry)
    {
        try {
            $this->journalService->postJournal($journalEntry->id);

            return redirect()->route('journal-entries.show', $journalEntry->id)
                ->with('success', "Journal entry {$journalEntry->journal_number} berhasil diposting.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reverse(JournalEntry $journalEntry, Request $request)
    {
        try {
            $reversalDate = $request->input('reversal_date');
            
            $reversal = $this->journalService->reverseJournal(
                $journalEntry->id,
                $reversalDate
            );

            return redirect()->route('journal-entries.show', $reversal->id)
                ->with('success', "Journal entry {$journalEntry->journal_number} berhasil di-reverse. Reversal entry: {$reversal->journal_number}");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
