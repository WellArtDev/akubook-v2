<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\DotMatrixTemplate;
use App\Models\GoodsReceipt;
use App\Models\PrintDraft;
use App\Models\PrintHistory;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PrintDraftController extends Controller
{
    public function index(Request $request)
    {
        $query = PrintDraft::with('template')->latest('id');

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where('draft_number', 'like', "%{$search}%");
        }

        return Inertia::render('PrintDrafts/Index', [
            'drafts' => $query->paginate(50)->withQueryString(),
            'filters' => $request->only(['search', 'document_type']),
            'documentTypes' => PrintDraft::DOCUMENT_TYPES,
        ]);
    }

    public function create(Request $request)
    {
        $documentType = $request->input('document_type', 'sales_invoice');

        return Inertia::render('PrintDrafts/Create', [
            'documentTypes' => PrintDraft::DOCUMENT_TYPES,
            'documentType' => $documentType,
            'templates' => DotMatrixTemplate::query()
                ->where('document_type', $documentType)
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(['id', 'name', 'is_default']),
            'sources' => $this->sourceDocuments($documentType),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateDraft($request);

        $draft = PrintDraft::create([
            'draft_number' => PrintDraft::generateNumber(),
            'document_type' => $validated['document_type'],
            'document_id' => $validated['document_id'],
            'dot_matrix_template_id' => $validated['dot_matrix_template_id'],
            'override_payload' => $validated['override_payload'],
            'status' => $validated['status'] ?? 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('print-drafts.show', $draft)
            ->with('success', 'Print draft created.');
    }

    public function show(PrintDraft $printDraft)
    {
        return Inertia::render('PrintDrafts/Show', [
            'draft' => $printDraft->load(['template', 'createdBy', 'updatedBy']),
            'source' => $this->sourceDocumentDetail($printDraft->document_type, $printDraft->document_id),
        ]);
    }

    public function edit(PrintDraft $printDraft)
    {
        return Inertia::render('PrintDrafts/Edit', [
            'draft' => $printDraft,
            'documentTypes' => PrintDraft::DOCUMENT_TYPES,
            'templates' => DotMatrixTemplate::query()
                ->where('document_type', $printDraft->document_type)
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(['id', 'name', 'is_default']),
            'sources' => $this->sourceDocuments($printDraft->document_type),
        ]);
    }

    public function update(Request $request, PrintDraft $printDraft)
    {
        $validated = $this->validateDraft($request);

        $printDraft->update([
            'document_type' => $validated['document_type'],
            'document_id' => $validated['document_id'],
            'dot_matrix_template_id' => $validated['dot_matrix_template_id'],
            'override_payload' => $validated['override_payload'],
            'status' => $validated['status'] ?? $printDraft->status,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('print-drafts.show', $printDraft)
            ->with('success', 'Print draft updated.');
    }

    public function preview(PrintDraft $printDraft)
    {
        $printDraft->load('template');

        return Inertia::render('PrintDrafts/Preview', [
            'draft' => $printDraft,
            'source' => $this->sourceDocumentDetail($printDraft->document_type, $printDraft->document_id),
            'previewLines' => $this->buildPreviewLines($printDraft),
        ]);
    }

    public function markReady(PrintDraft $printDraft)
    {
        $printDraft->update([
            'status' => 'ready',
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('print-drafts.preview', $printDraft)
            ->with('success', 'Print draft marked as ready.');
    }

    public function recordPrint(PrintDraft $printDraft)
    {
        $printDraft->load('template');

        PrintHistory::create([
            'print_draft_id' => $printDraft->id,
            'document_type' => $printDraft->document_type,
            'document_id' => $printDraft->document_id,
            'dot_matrix_template_id' => $printDraft->dot_matrix_template_id,
            'printed_by' => Auth::id(),
            'printed_at' => now(),
            'output_metadata' => [
                'columns' => $printDraft->template?->columns,
                'rows' => $printDraft->template?->rows,
                'draft_status' => $printDraft->status,
            ],
        ]);

        return redirect()->route('print-histories.index')
            ->with('success', 'Print history recorded.');
    }

    public function destroy(PrintDraft $printDraft)
    {
        $printDraft->delete();

        return redirect()->route('print-drafts.index')
            ->with('success', 'Print draft deleted.');
    }

    private function buildPreviewLines(PrintDraft $printDraft): array
    {
        $columns = max((int) $printDraft->template->columns, 1);
        $rows = max((int) $printDraft->template->rows, 1);
        $lines = array_fill(0, $rows, str_repeat(' ', $columns));
        $payload = $printDraft->override_payload ?? [];

        foreach ($printDraft->template->field_map ?? [] as $field) {
            $x = max((int) ($field['x'] ?? 1), 1) - 1;
            $y = max((int) ($field['y'] ?? 1), 1) - 1;
            $width = max((int) ($field['width'] ?? 20), 1);

            if ($y >= $rows || $x >= $columns) {
                continue;
            }

            $value = (string) $this->previewValue($payload, $field['field'] ?? '', $field['label'] ?? '');
            $value = substr($value, 0, min($width, $columns - $x));
            $line = $lines[$y];
            $lines[$y] = substr($line, 0, $x) . $value . substr($line, $x + strlen($value));
        }

        return array_map(static fn (string $line) => rtrim($line), $lines);
    }

    private function previewValue(array $payload, string $field, string $label): string
    {
        if (array_key_exists($field, $payload)) {
            return (string) $payload[$field];
        }

        if (array_key_exists($field, $payload['header'] ?? [])) {
            return (string) $payload['header'][$field];
        }

        return $label;
    }

    private function validateDraft(Request $request): array
    {
        return $request->validate([
            'document_type' => 'required|in:' . implode(',', PrintDraft::DOCUMENT_TYPES),
            'document_id' => 'required|integer|min:1',
            'dot_matrix_template_id' => 'required|exists:dot_matrix_templates,id',
            'override_payload' => 'required|array',
            'status' => 'nullable|in:draft,ready',
        ]);
    }

    private function sourceDocuments(string $documentType): array
    {
        return match ($documentType) {
            'sales_invoice' => SalesInvoice::query()->latest('id')->limit(50)->get(['id', 'invoice_number as number'])->toArray(),
            'delivery_order' => DeliveryOrder::query()->latest('id')->limit(50)->get(['id', 'do_number as number'])->toArray(),
            'purchase_order' => PurchaseOrder::query()->latest('id')->limit(50)->get(['id', 'po_number as number'])->toArray(),
            'goods_receipt' => GoodsReceipt::query()->latest('id')->limit(50)->get(['id', 'gr_number as number'])->toArray(),
            default => [],
        };
    }

    private function sourceDocumentDetail(string $documentType, int $documentId): ?array
    {
        $model = match ($documentType) {
            'sales_invoice' => SalesInvoice::query()->find($documentId),
            'delivery_order' => DeliveryOrder::query()->find($documentId),
            'purchase_order' => PurchaseOrder::query()->find($documentId),
            'goods_receipt' => GoodsReceipt::query()->find($documentId),
            default => null,
        };

        if (!$model) {
            return null;
        }

        return [
            'id' => $model->id,
            'number' => $model->invoice_number ?? $model->do_number ?? $model->po_number ?? $model->gr_number,
        ];
    }
}
