<?php

namespace App\Http\Controllers;

use App\Models\DotMatrixTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DotMatrixTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = DotMatrixTemplate::query()->orderBy('document_type')->orderBy('name');

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where('name', 'like', "%{$search}%");
        }

        return Inertia::render('DotMatrixTemplates/Index', [
            'templates' => $query->paginate(50)->withQueryString(),
            'filters' => $request->only(['search', 'document_type']),
            'documentTypes' => DotMatrixTemplate::DOCUMENT_TYPES,
        ]);
    }

    public function create(Request $request)
    {
        $documentType = $request->input('document_type', 'sales_invoice');

        return Inertia::render('DotMatrixTemplates/Create', [
            'documentTypes' => DotMatrixTemplate::DOCUMENT_TYPES,
            'defaultDocumentType' => $documentType,
            'defaultFieldMap' => DotMatrixTemplate::defaultFieldMap($documentType),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateTemplate($request);

        if (!empty($validated['is_default'])) {
            DotMatrixTemplate::where('document_type', $validated['document_type'])->update(['is_default' => false]);
        }

        $template = DotMatrixTemplate::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('dot-matrix-templates.show', $template)
            ->with('success', 'Dot matrix template created.');
    }

    public function show(DotMatrixTemplate $dotMatrixTemplate)
    {
        return Inertia::render('DotMatrixTemplates/Show', [
            'template' => $dotMatrixTemplate->load(['createdBy', 'updatedBy']),
            'sampleData' => $this->sampleData($dotMatrixTemplate->document_type),
        ]);
    }

    public function edit(DotMatrixTemplate $dotMatrixTemplate)
    {
        return Inertia::render('DotMatrixTemplates/Edit', [
            'template' => $dotMatrixTemplate,
            'documentTypes' => DotMatrixTemplate::DOCUMENT_TYPES,
        ]);
    }

    public function update(Request $request, DotMatrixTemplate $dotMatrixTemplate)
    {
        $validated = $this->validateTemplate($request);

        if (!empty($validated['is_default'])) {
            DotMatrixTemplate::where('document_type', $validated['document_type'])
                ->where('id', '!=', $dotMatrixTemplate->id)
                ->update(['is_default' => false]);
        }

        $dotMatrixTemplate->update([
            ...$validated,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('dot-matrix-templates.show', $dotMatrixTemplate)
            ->with('success', 'Dot matrix template updated.');
    }

    public function destroy(DotMatrixTemplate $dotMatrixTemplate)
    {
        $dotMatrixTemplate->delete();

        return redirect()->route('dot-matrix-templates.index')
            ->with('success', 'Dot matrix template deleted.');
    }

    public function defaults(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:' . implode(',', DotMatrixTemplate::DOCUMENT_TYPES),
        ]);

        return response()->json([
            'field_map' => DotMatrixTemplate::defaultFieldMap($validated['document_type']),
        ]);
    }

    private function validateTemplate(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'document_type' => 'required|in:' . implode(',', DotMatrixTemplate::DOCUMENT_TYPES),
            'paper_size' => 'required|string|max:50',
            'columns' => 'required|integer|min:20|max:240',
            'rows' => 'required|integer|min:10|max:200',
            'margins' => 'required|array',
            'margins.top' => 'required|integer|min:0|max:20',
            'margins.left' => 'required|integer|min:0|max:40',
            'margins.right' => 'required|integer|min:0|max:40',
            'margins.bottom' => 'required|integer|min:0|max:20',
            'field_map' => 'required|array|min:1',
            'field_map.*.field' => 'required|string|max:100',
            'field_map.*.x' => 'required|integer|min:0|max:240',
            'field_map.*.y' => 'required|integer|min:0|max:200',
            'field_map.*.width' => 'nullable|integer|min:1|max:240',
            'field_map.*.label' => 'nullable|string|max:100',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);
    }

    private function sampleData(string $documentType): array
    {
        return match ($documentType) {
            'sales_invoice' => [
                'invoice_number' => 'INV-2026-0001',
                'invoice_date' => '2026-05-18',
                'customer_name' => 'PT Contoh Customer',
                'grand_total' => 'Rp 1.110.000',
            ],
            'delivery_order' => [
                'do_number' => 'DO-2026-0001',
                'do_date' => '2026-05-18',
                'customer_name' => 'PT Contoh Customer',
                'status' => 'delivered',
            ],
            'purchase_order' => [
                'po_number' => 'PO-2026-0001',
                'order_date' => '2026-05-18',
                'supplier_name' => 'PT Contoh Supplier',
                'grand_total' => 'Rp 2.220.000',
            ],
            'goods_receipt' => [
                'gr_number' => 'GR-2026-0001',
                'gr_date' => '2026-05-18',
                'supplier_name' => 'PT Contoh Supplier',
                'status' => 'received',
            ],
            default => [],
        };
    }
}
