<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['employee', 'creator', 'approver', 'rejector', 'canceller']);

        if ($request->filled('search')) {
            $search = $request->string('search')->value();
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->value());
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->string('date_from')->value());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->string('date_to')->value());
        }

        return Inertia::render('LeaveRequests/Index', [
            'leaveRequests' => $query->orderByDesc('id')->paginate(50)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
            'statuses' => LeaveRequest::STATUSES,
        ]);
    }

    public function create()
    {
        return Inertia::render('LeaveRequests/Create', [
            'employees' => Employee::query()
                ->where('employment_status', 'active')
                ->orderBy('employee_id')
                ->get(['id', 'employee_id', 'full_name']),
            'leaveTypes' => ['annual', 'sick', 'unpaid', 'special'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'leave_type' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string'],
        ]);

        $start = \Illuminate\Support\Carbon::parse($validated['start_date']);
        $end = \Illuminate\Support\Carbon::parse($validated['end_date']);

        LeaveRequest::create([
            ...$validated,
            'total_days' => $start->diffInDays($end) + 1,
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request created.');
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['employee', 'creator', 'approver', 'rejector', 'canceller']);

        return Inertia::render('LeaveRequests/Show', [
            'leaveRequest' => $leaveRequest,
        ]);
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending leave can be approved.']);
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending leave can be rejected.']);
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Leave request rejected.');
    }

    public function cancel(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending leave can be cancelled.']);
        }

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:1000'],
        ]);

        $leaveRequest->update([
            'status' => 'cancelled',
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        return back()->with('success', 'Leave request cancelled.');
    }
}
