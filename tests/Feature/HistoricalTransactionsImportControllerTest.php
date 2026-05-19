<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\FiscalPeriod;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalTransactionsImportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_endpoint_returns_summary(): void
    {
        $payload = $this->seedFixture();
        $user = User::query()->first();

        $response = $this->actingAs($user)->postJson(route('migration.historical-transactions.preview'), $payload);

        $response->assertOk()->assertJsonPath('total', 3)->assertJsonPath('skipped', 0);
    }

    public function test_import_endpoint_persists_transactions(): void
    {
        $payload = $this->seedFixture();
        $user = User::query()->first();

        $response = $this->actingAs($user)->postJson(route('migration.historical-transactions.import'), $payload);

        $response->assertOk()->assertJsonPath('executed', true)->assertJsonPath('imported', 3);

        $this->assertDatabaseHas('sales_orders', ['so_number' => 'SO-HIS-001']);
        $this->assertDatabaseHas('purchase_orders', ['po_number' => 'PO-HIS-001']);
        $this->assertDatabaseHas('customer_payments', ['payment_number' => 'CP-HIS-001']);
    }

    private function seedFixture(): array
    {
        $user = User::factory()->create();

        FiscalPeriod::create([
            'name' => '2026-01',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'open',
            'is_current' => true,
        ]);

        Customer::create([
            'code' => 'CUST-001',
            'name' => 'PT Customer',
            'is_active' => true,
        ]);

        Supplier::create([
            'supplier_code' => 'SUP-001',
            'name' => 'PT Supplier',
            'category' => 'local',
            'tax_type' => 'pkp',
            'payment_terms' => 30,
            'is_active' => true,
            'delivery_rating' => 5,
            'quality_rating' => 5,
        ]);

        Item::create([
            'code' => 'ITEM-001',
            'name' => 'Produk A',
            'item_type' => 'goods',
            'unit' => 'pcs',
            'is_active' => true,
        ]);

        Account::create([
            'code' => '1-1000',
            'name' => 'Kas',
            'type' => 'asset',
            'category' => 'current_asset',
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);

        Account::create([
            'code' => '4-1000',
            'name' => 'Penjualan',
            'type' => 'revenue',
            'category' => 'operating_revenue',
            'is_header' => false,
            'is_active' => true,
            'balance' => 0,
        ]);

        return [
            'transactions' => [
                'sales_orders' => [[
                    'so_number' => 'SO-HIS-001',
                    'so_date' => '2026-01-15',
                    'customer_code' => 'CUST-001',
                    'status' => 'approved',
                    'created_by' => $user->id,
                    'sales_person_id' => $user->id,
                    'lines' => [[
                        'item_code' => 'ITEM-001',
                        'quantity' => 2,
                        'unit' => 'pcs',
                        'unit_price' => 10000,
                        'line_total' => 20000,
                    ]],
                    'journal_lines' => [
                        ['account_code' => '1-1000', 'debit' => 20000, 'credit' => 0],
                        ['account_code' => '4-1000', 'debit' => 0, 'credit' => 20000],
                    ],
                ]],
                'purchase_orders' => [[
                    'po_number' => 'PO-HIS-001',
                    'po_date' => '2026-01-16',
                    'supplier_code' => 'SUP-001',
                    'status' => 'approved',
                    'created_by' => $user->id,
                    'lines' => [[
                        'product_code' => 'ITEM-001',
                        'product_name' => 'Produk A',
                        'quantity' => 1,
                        'unit' => 'pcs',
                        'unit_price' => 20000,
                        'line_total' => 20000,
                    ]],
                    'journal_lines' => [
                        ['account_code' => '1-1000', 'debit' => 20000, 'credit' => 0],
                        ['account_code' => '4-1000', 'debit' => 0, 'credit' => 20000],
                    ],
                ]],
                'customer_payments' => [[
                    'payment_number' => 'CP-HIS-001',
                    'payment_date' => '2026-01-17',
                    'customer_code' => 'CUST-001',
                    'payment_method' => 'cash',
                    'total_amount' => 15000,
                    'status' => 'posted',
                    'created_by' => $user->id,
                    'journal_lines' => [
                        ['account_code' => '1-1000', 'debit' => 15000, 'credit' => 0],
                        ['account_code' => '4-1000', 'debit' => 0, 'credit' => 15000],
                    ],
                ]],
            ],
        ];
    }
}
