<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::query()
            ->with('actor:id,name,email')
            ->when($request->filled('event_key'), fn ($query) => $query->where('event_key', $request->event_key))
            ->when($request->filled('entity_type'), fn ($query) => $query->where('entity_type', $request->entity_type))
            ->when($request->filled('actor_user_id'), fn ($query) => $query->where('actor_user_id', $request->actor_user_id))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('occurred_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('occurred_at', '<=', $request->date_to))
            ->latest('occurred_at')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['event_key', 'entity_type', 'actor_user_id', 'date_from', 'date_to']),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'eventKeys' => AuditLog::query()->select('event_key')->distinct()->orderBy('event_key')->pluck('event_key'),
            'entityTypes' => AuditLog::query()->select('entity_type')->distinct()->orderBy('entity_type')->pluck('entity_type'),
        ]);
    }
}
