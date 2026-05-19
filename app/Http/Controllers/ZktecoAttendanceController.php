<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\ZktecoAttendanceLog;
use App\Models\ZktecoDevice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ZktecoAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $logs = ZktecoAttendanceLog::query()
            ->with(['device', 'employee', 'attendanceRecord'])
            ->when($request->filled('device_id'), fn ($query) => $query->where('zkteco_device_id', $request->device_id))
            ->when($request->filled('employee_identifier'), fn ($query) => $query->where('employee_identifier', 'like', '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->employee_identifier) . '%'))
            ->when($request->filled('is_mapped'), fn ($query) => $query->where('is_mapped', $request->boolean('is_mapped')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('punch_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('punch_at', '<=', $request->date_to))
            ->latest('punch_at')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('ZktecoAttendance/Index', [
            'logs' => $logs,
            'devices' => $this->devices(),
            'filters' => $request->only(['device_id', 'employee_identifier', 'is_mapped', 'date_from', 'date_to']),
        ]);
    }

    public function create()
    {
        return Inertia::render('ZktecoAttendance/Create', [
            'devices' => $this->devices(),
            'punchTypes' => ZktecoAttendanceLog::PUNCH_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'zkteco_device_id' => ['required', 'exists:zkteco_devices,id'],
            'employee_identifier' => ['required', 'string', 'max:50'],
            'punch_at' => ['required', 'date'],
            'punch_type' => ['required', Rule::in(ZktecoAttendanceLog::PUNCH_TYPES)],
            'notes' => ['nullable', 'string'],
        ]);

        $sourceKey = $this->sourceKey($data['zkteco_device_id'], $data['employee_identifier'], $data['punch_at'], $data['punch_type']);
        $existing = ZktecoAttendanceLog::where('source_key', $sourceKey)->first();

        if ($existing) {
            return redirect()->route('zkteco-attendance.show', $existing)->with('success', 'Log duplikat diabaikan.');
        }

        $log = DB::transaction(function () use ($data, $sourceKey) {
            $employee = Employee::where('employee_id', $data['employee_identifier'])->where('employment_status', 'active')->first();
            $attendance = $employee ? $this->syncAttendance($employee, $data['punch_at'], $data['punch_type'], $data['notes'] ?? null) : null;

            return ZktecoAttendanceLog::create([
                'zkteco_device_id' => $data['zkteco_device_id'],
                'employee_identifier' => $data['employee_identifier'],
                'punch_at' => $data['punch_at'],
                'punch_type' => $data['punch_type'],
                'employee_id' => $employee?->id,
                'attendance_record_id' => $attendance?->id,
                'is_mapped' => (bool) $employee,
                'source_key' => $sourceKey,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('zkteco-attendance.show', $log)->with('success', 'Log ZKTeco diimpor.');
    }

    public function show(ZktecoAttendanceLog $zktecoAttendance)
    {
        $zktecoAttendance->load(['device', 'employee', 'attendanceRecord', 'creator']);

        return Inertia::render('ZktecoAttendance/Show', [
            'log' => $zktecoAttendance,
        ]);
    }

    public function devicesIndex(Request $request)
    {
        $devices = ZktecoDevice::query()
            ->with('creator')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->search) . '%';
                $query->where(fn ($sub) => $sub->where('device_code', 'like', $search)->orWhere('name', 'like', $search)->orWhere('ip_address', 'like', $search));
            })
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('device_code')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('ZktecoDevices/Index', [
            'devices' => $devices,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    public function devicesCreate()
    {
        return Inertia::render('ZktecoDevices/Create');
    }

    public function devicesStore(Request $request)
    {
        $data = $this->validateDevice($request);
        $data['created_by'] = Auth::id();

        $device = ZktecoDevice::create($data);

        return redirect()->route('zkteco-devices.show', $device)->with('success', 'Device ZKTeco dibuat.');
    }

    public function devicesShow(ZktecoDevice $zktecoDevice)
    {
        $zktecoDevice->load('creator', 'updater');

        return Inertia::render('ZktecoDevices/Show', [
            'device' => $zktecoDevice,
            'log_count' => $zktecoDevice->logs()->count(),
        ]);
    }

    public function devicesDestroy(ZktecoDevice $zktecoDevice)
    {
        $zktecoDevice->update(['is_active' => false, 'updated_by' => Auth::id()]);

        return redirect()->route('zkteco-devices.index')->with('success', 'Device ZKTeco dinonaktifkan.');
    }

    private function syncAttendance(Employee $employee, string $punchAt, string $punchType, ?string $notes): AttendanceRecord
    {
        $time = Carbon::parse($punchAt);
        $attendance = AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $time->toDateString())
            ->first();

        if (!$attendance) {
            $attendance = AttendanceRecord::create([
                'employee_id' => $employee->id,
                'attendance_date' => $time->toDateString(),
                'status' => 'incomplete',
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);
        }

        if ($punchType === 'check_in' && (!$attendance->check_in_at || $time->lt($attendance->check_in_at))) {
            $attendance->check_in_at = $time;
            $attendance->status = $attendance->check_out_at ? 'present' : 'incomplete';
        }

        if ($punchType === 'check_out' && (!$attendance->check_out_at || $time->gt($attendance->check_out_at))) {
            $attendance->check_out_at = $time;
        }

        if ($attendance->check_in_at && $attendance->check_out_at && $attendance->check_out_at->gt($attendance->check_in_at)) {
            $attendance->work_hours = round($attendance->check_in_at->diffInMinutes($attendance->check_out_at) / 60, 2);
            $attendance->status = 'present';
        }

        $attendance->updated_by = Auth::id();
        $attendance->save();

        return $attendance;
    }

    private function sourceKey(int $deviceId, string $identifier, string $punchAt, string $punchType): string
    {
        return hash('sha256', implode('|', [$deviceId, $identifier, Carbon::parse($punchAt)->toDateTimeString(), $punchType]));
    }

    private function validateDevice(Request $request): array
    {
        return $request->validate([
            'device_code' => ['required', 'string', 'max:50', 'unique:zkteco_devices,device_code'],
            'name' => ['required', 'string', 'max:255'],
            'ip_address' => ['required', 'ip'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function devices()
    {
        return ZktecoDevice::where('is_active', true)->orderBy('device_code')->get(['id', 'device_code', 'name', 'ip_address']);
    }
}
