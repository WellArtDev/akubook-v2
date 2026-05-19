<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\EFakturExport;
use App\Models\FakturPajak;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EFakturExportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private SalesInvoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->customer = Customer::factory()->create([
            'name' => 'PT Pajak Maju',
            'tax_id' => '01.234.567.8-999.000',
        ]);
        $this->invoice = SalesInvoice::factory()->create([
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-TAX-001',
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 11000,
            'grand_total' => 111000,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_index_page_can_be_opened(): void
    {
        EFakturExport::factory()->create(['created_by' => $this->user->id]);

        $this->get(route('e-faktur-exports.index'))->assertOk();
    }

    public function test_user_can_generate_export_from_issued_faktur(): void
    {
        $faktur = $this->issuedFaktur();

        $response = $this->post(route('e-faktur-exports.store'), [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('e_faktur_exports', [
            'status' => 'generated',
            'row_count' => 1,
            'created_by' => $this->user->id,
        ]);
        $this->assertDatabaseHas('e_faktur_export_lines', [
            'faktur_pajak_id' => $faktur->id,
            'faktur_number' => $faktur->faktur_number,
            'customer_name' => 'PT Pajak Maju',
            'customer_tax_id' => '01.234.567.8-999.000',
            'dpp' => 100000,
            'ppn_amount' => 11000,
            'grand_total' => 111000,
        ]);
    }

    public function test_draft_or_cancelled_faktur_are_not_exported(): void
    {
        $this->issuedFaktur(['status' => 'draft']);
        $this->issuedFaktur(['status' => 'cancelled', 'faktur_number' => 'FP-' . now()->year . '-9999']);

        $this->post(route('e-faktur-exports.store'), [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('e_faktur_exports', ['row_count' => 0]);
        $this->assertDatabaseCount('e_faktur_export_lines', 0);
    }

    public function test_export_download_returns_csv_content(): void
    {
        $this->issuedFaktur();
        $this->post(route('e-faktur-exports.store'), [
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
        ]);

        $export = EFakturExport::query()->firstOrFail();

        $this->get(route('e-faktur-exports.download', $export))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    private function issuedFaktur(array $overrides = []): FakturPajak
    {
        return FakturPajak::query()->create(array_merge([
            'faktur_number' => FakturPajak::generateNumber(),
            'faktur_date' => now()->toDateString(),
            'sales_invoice_id' => $this->invoice->id,
            'customer_id' => $this->customer->id,
            'dpp' => 100000,
            'ppn_amount' => 11000,
            'grand_total' => 111000,
            'status' => 'issued',
            'created_by' => $this->user->id,
            'issued_by' => $this->user->id,
            'issued_at' => now(),
        ], $overrides));
    }
}
