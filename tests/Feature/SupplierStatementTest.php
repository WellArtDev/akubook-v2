<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierStatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_loads_and_shows_filters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('supplier-statements.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('SupplierStatements/Index')
            ->has('suppliers')
            ->where('statement', null)
        );
    }

    public function test_statement_calculates_closing_balance(): void
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
            'status' => 'approved',
        ]);

        $gr = GoodsReceipt::create([
            'gr_number' => 'GR-' . now()->year . '-0009',
            'gr_date' => now()->toDateString(),
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'status' => 'received',
            'created_by' => $user->id,
        ]);

        PurchaseInvoice::factory()->create([
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => $gr->id,
            'invoice_date' => now()->subDays(5)->toDateString(),
            'total_amount' => 1000,
            'paid_amount' => 0,
            'outstanding_amount' => 1000,
            'created_by' => $user->id,
        ]);

        SupplierPayment::factory()->create([
            'supplier_id' => $supplier->id,
            'payment_date' => now()->subDays(3)->toDateString(),
            'status' => 'posted',
            'total_amount' => 300,
            'created_by' => $user->id,
        ]);

        PurchaseReturn::create([
            'return_number' => 'PRET-' . now()->year . '-0001',
            'return_date' => now()->subDays(2)->toDateString(),
            'purchase_invoice_id' => PurchaseInvoice::query()->first()->id,
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'return_reason' => 'Test return',
            'status' => 'draft',
            'subtotal' => 100,
            'tax_amount' => 0,
            'total_amount' => 100,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('supplier-statements.index', [
            'supplier_id' => $supplier->id,
            'date_from' => now()->subDays(10)->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertInertia(fn ($page) => $page
            ->component('SupplierStatements/Index')
            ->where('statement.closing_balance', 600)
            ->has('statement.transactions', 3)
        );
    }

    public function test_pdf_endpoint_returns_json_payload(): void
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($user)->get(route('supplier-statements.pdf', [
            'supplier_id' => $supplier->id,
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertJsonPath('format', 'pdf-ready-json');
    }
}
