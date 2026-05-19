<?php

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\FiscalPeriod;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use App\Services\HistoricalTransactionsImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalTransactionsImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_multi_module_success(): void
    {
        $fixture = $this->seedFixture();

        $service = new HistoricalTransactionsImportService;
        $result = $service->preview($fixture['payload']);

        $this->assertFalse($result['executed']);
        $this->assertSame(3, $result['total']);
        $this->assertSame(3, $result['valid']);
        $this->assertSame(0, $result['skipped']);
    }

    public function test_import_multi_module_persists_and_journal_balanced(): void
    {
        $fixture = $this->seedFixture();

        $service = new HistoricalTransactionsImportService;
        $result = $service->import($fixture['payload']);

        $this->assertTrue($result['executed']);
        $this->assertSame(3, $result['imported']);
        $this->assertSame(3, $result['journal_count']);

        $this->assertDatabaseHas('sales_orders', ['so_number' => 'SO-HIS-001']);
        $this->assertDatabaseHas('purchase_orders', ['po_number' => 'PO-HIS-001']);
        $this->assertDatabaseHas('customer_payments', ['payment_number' => 'CP-HIS-001']);

        $journal = \App\Models\JournalEntry::where('reference_type', 'sales_order')->first();
        $this->assertNotNull($journal);
        $this->assertEquals((float) $journal->total_debit, (float) $journal->total_credit);
    }

    public function test_invalid_reference_skipped_with_reason(): void
    {
        $fixture = $this->seedFixture();
        $fixture['payload']['transactions']['sales_orders'][0]['customer_code'] = 'UNKNOWN';

        $service = new HistoricalTransactionsImportService;
        $preview = $service->preview($fixture['payload']);

        $this->assertSame(1, $preview['skipped']);
        $this->assertNotEmpty($preview['errors']);
        $this->assertStringContainsString('Customer tidak ditemukan', implode(' ', $preview['errors'][0]['errors']));
    }

    public function test_import_skips_invalid_rows_and_persists_valid_rows(): void
    {
        $fixture = $this->seedFixture();
        $fixture['payload']['transactions']['sales_orders'][] = $fixture['payload']['transactions']['sales_orders'][0];
        $fixture['payload']['transactions']['sales_orders'][1]['so_number'] = 'SO-HIS-INVALID';
        $fixture['payload']['transactions']['sales_orders'][1]['customer_code'] = 'UNKNOWN';

        $service = new HistoricalTransactionsImportService;
        $result = $service->import($fixture['payload']);

        $this->assertTrue($result['executed']);
        $this->assertSame(3, $result['imported']);
        $this->assertSame(1, $result['skipped']);
        $this->assertDatabaseHas('sales_orders', ['so_number' => 'SO-HIS-001']);
        $this->assertDatabaseMissing('sales_orders', ['so_number' => 'SO-HIS-INVALID']);
    }

    public function test_unbalanced_posted_journal_rejected(): void
    {
        $fixture = $this->seedFixture();
        $fixture['payload']['transactions']['purchase_orders'][0]['journal_lines'][1]['credit'] = 10000;

        $service = new HistoricalTransactionsImportService;
        $result = $service->import($fixture['payload']);

        $this->assertTrue($result['executed']);
        $this->assertSame(1, $result['skipped']);
        $this->assertSame(2, $result['imported']);
        $this->assertDatabaseMissing('purchase_orders', ['po_number' => 'PO-HIS-001']);
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
            'payload' => [
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
            ],
        ];
    }
}
