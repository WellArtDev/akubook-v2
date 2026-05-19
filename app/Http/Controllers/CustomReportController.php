<?php

namespace App\Http\Controllers;

use App\Models\CustomReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CustomReportController extends Controller
{
    private const SOURCE_TABLES = [
        'employees' => 'employees',
        'sales_invoices' => 'sales_invoices',
        'purchase_orders' => 'purchase_orders',
        'vouchers' => 'vouchers',
        'attendance_records' => 'attendance_records',
    ];

    private const SEARCH_COLUMNS = [
        'employees' => ['employee_id', 'full_name'],
        'sales_invoices' => ['invoice_number', 'status'],
        'purchase_orders' => ['po_number', 'status'],
        'vouchers' => ['voucher_number', 'reference_number'],
        'attendance_records' => ['status'],
    ];

    private const DATE_COLUMNS = [
        'employees' => 'join_date',
        'sales_invoices' => 'invoice_date',
        'purchase_orders' => 'po_date',
        'vouchers' => 'voucher_date',
        'attendance_records' => 'attendance_date',
    ];

    public function index(Request $request)
    {
        $reports = CustomReport::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $request->search) . '%';
                $query->where(fn ($q) => $q->where('code', 'like', $search)->orWhere('name', 'like', $search));
            })
            ->when($request->filled('source_key'), fn ($query) => $query->where('source_key', $request->source_key))
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('CustomReports/Index', [
            'reports' => $reports,
            'filters' => $request->only(['search', 'source_key', 'is_active']),
            'sources' => $this->sources(),
        ]);
    }

    public function create()
    {
        return Inertia::render('CustomReports/Create', [
            'sources' => $this->sources(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateReport($request);
        $data['created_by'] = Auth::id();

        $report = CustomReport::create($data);

        return redirect()->route('custom-reports.show', $report)->with('success', 'Custom report dibuat.');
    }

    public function show(CustomReport $customReport, Request $request)
    {
        return Inertia::render('CustomReports/Show', [
            'report' => $customReport,
            'preview' => $this->preview($customReport, $request),
            'filters' => $request->only(['search', 'date_from', 'date_to']),
        ]);
    }

    public function edit(CustomReport $customReport)
    {
        return Inertia::render('CustomReports/Edit', [
            'report' => $customReport,
            'sources' => $this->sources(),
        ]);
    }

    public function update(Request $request, CustomReport $customReport)
    {
        $data = $this->validateReport($request, $customReport);
        $data['updated_by'] = Auth::id();
        $customReport->update($data);

        return redirect()->route('custom-reports.show', $customReport)->with('success', 'Custom report diperbarui.');
    }

    public function destroy(CustomReport $customReport)
    {
        $customReport->delete();

        return redirect()->route('custom-reports.index')->with('success', 'Custom report dihapus.');
    }

    private function validateReport(Request $request, ?CustomReport $customReport = null): array
    {
        $sourceKeys = array_keys(CustomReport::SOURCES);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('custom_reports', 'code')->ignore($customReport)],
            'name' => ['required', 'string', 'max:150'],
            'source_key' => ['required', Rule::in($sourceKeys)],
            'selected_columns' => ['required', 'array', 'min:1'],
            'selected_columns.*' => ['required', 'string'],
            'default_filters' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $allowedColumns = CustomReport::SOURCES[$data['source_key']];
        $invalid = array_diff($data['selected_columns'], $allowedColumns);

        if (! empty($invalid)) {
            abort(422, 'Kolom tidak valid untuk source report.');
        }

        return $data;
    }

    private function preview(CustomReport $report, Request $request): array
    {
        $source = $report->source_key;
        $table = self::SOURCE_TABLES[$source];
        $columns = array_values(array_intersect($report->selected_columns, CustomReport::SOURCES[$source]));

        $query = DB::table($table)->select($columns)->whereNull($table . '.deleted_at');

        $dateColumn = self::DATE_COLUMNS[$source];
        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = '%' . strtolower($request->search) . '%';
            $query->where(function ($q) use ($source, $search) {
                foreach (self::SEARCH_COLUMNS[$source] as $column) {
                    $q->orWhereRaw('LOWER(' . $column . ') LIKE ?', [$search]);
                }
            });
        }

        foreach (($report->default_filters ?? []) as $key => $value) {
            if ($value !== null && in_array($key, CustomReport::SOURCES[$source], true)) {
                $query->where($key, $value);
            }
        }

        return [
            'columns' => $columns,
            'rows' => $query->limit(200)->get()->map(fn ($row) => (array) $row)->values(),
            'generated_at' => now()->toISOString(),
        ];
    }

    private function sources(): array
    {
        return collect(CustomReport::SOURCES)
            ->map(fn ($columns, $key) => ['key' => $key, 'label' => str($key)->replace('_', ' ')->title()->toString(), 'columns' => $columns])
            ->values()
            ->all();
    }
}
