<?php

namespace App\Http\Controllers;

use App\Models\WorkShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class WorkShiftController extends Controller
{
    public function index(Request $request)
    {
        $shifts = WorkShift::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->search) . '%';
                $query->where(fn ($sub) => $sub->where('shift_code', 'like', $search)->orWhere('name', 'like', $search));
            })
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('shift_code')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('WorkShifts/Index', [
            'shifts' => $shifts,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    public function create()
    {
        return Inertia::render('WorkShifts/Create');
    }

    public function store(Request $request)
    {
        $data = $this->validateShift($request);
        $data['created_by'] = Auth::id();

        $shift = WorkShift::create($data);

        return redirect()->route('work-shifts.show', $shift)->with('success', 'Shift dibuat.');
    }

    public function show(WorkShift $workShift)
    {
        $workShift->load('creator');

        return Inertia::render('WorkShifts/Show', [
            'shift' => $workShift,
            'active_assignment_count' => $workShift->assignments()->where('status', 'active')->count(),
        ]);
    }

    public function edit(WorkShift $workShift)
    {
        return Inertia::render('WorkShifts/Edit', [
            'shift' => $workShift,
        ]);
    }

    public function update(Request $request, WorkShift $workShift)
    {
        $data = $this->validateShift($request, $workShift);
        $data['updated_by'] = Auth::id();

        $workShift->update($data);

        return redirect()->route('work-shifts.show', $workShift)->with('success', 'Shift diperbarui.');
    }

    public function destroy(WorkShift $workShift)
    {
        $workShift->update(['is_active' => false, 'updated_by' => Auth::id()]);

        return redirect()->route('work-shifts.index')->with('success', 'Shift dinonaktifkan.');
    }

    private function validateShift(Request $request, ?WorkShift $shift = null): array
    {
        return $request->validate([
            'shift_code' => ['required', 'string', 'max:50', Rule::unique('work_shifts', 'shift_code')->ignore($shift?->id)],
            'name' => ['required', 'string', 'max:255'],
            'check_in_time' => ['required', 'date_format:H:i'],
            'check_out_time' => ['required', 'date_format:H:i'],
            'tolerance_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'is_overnight' => ['boolean'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
