<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\FakturPajak;
use App\Models\SalesInvoice;
use App\Models\TaxCalculation;
use App\Models\TaxConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxReportingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->customer = Customer::factory()->create();
    }

    public function test_index_page_can_be_opened(): void
    {
        $this->get(route('tax-reports.index'))->assertOk();
    }

    public function test_report_calculates_tax_summary(): void
    {
        $invoice = SalesInvoice::factory()->create([
            'customer_id' => $this->customer->id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 11000,
            'grand_total' => 111000,
            'created_by' => $this->user->id,
        ]);

        FakturPajak::query()->create([
            'faktur_number' => FakturPajak::generateNumber(),
            'faktur_date' => now()->toDateString(),
            'sales_invoice_id' => $invoice->id,
            'customer_id' => $this->customer->id,
            'dpp' => 100000,
            'ppn_amount' => 11000,
            'grand_total' => 111000,
            'status' => 'issued',
            'created_by' => $this->user->id,
            'issued_by' => $this->user->id,
            'issued_at' => now(),
        ]);

        $account = Account::factory()->create([
            'type' => 'liability',
            'category' => 'current_liability',
            'is_header' => false,
            'is_active' => true,
        ]);

        $config = TaxConfiguration::query()->create([
            'code' => 'TIN-11',
            'name' => 'PPN Input 11%',
            'tax_type' => 'ppn_in',
            'rate' => 11,
            'account_id' => $account->id,
            'is_default' => true,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        TaxCalculation::query()->create([
            'tax_configuration_id' => $config->id,
            'tax_type' => 'ppn_in',
            'taxable_amount' => 50000,
            'is_inclusive' => false,
            'rate' => 11,
            'dpp' => 50000,
            'tax_amount' => 5500,
            'grand_total' => 55500,
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('tax-reports.index', [
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->endOfMonth()->toDateString(),
        ]));

        $response->assertOk();
        $summary = $response->viewData('page')['props']['summary'];
        $this->assertEquals(11000.0, $summary['ppn_out']);
        $this->assertEquals(5500.0, $summary['ppn_in']);
        $this->assertEquals(5500.0, $summary['net_vat']);
    }
}
