<?php

namespace Tests\Unit\Services;

use App\Services\MasterDataImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_master_data_successfully(): void
    {
        $service = new MasterDataImportService();

        $result = $service->preview([
            'customers' => [
                ['code' => 'CUST-001', 'name' => 'PT Alpha', 'customer_type' => 'company'],
            ],
            'suppliers' => [
                ['supplier_code' => 'SUPP-001', 'name' => 'PT Vendor', 'tax_type' => 'pkp'],
            ],
            'items' => [
                ['code' => 'ITEM-001', 'name' => 'Produk A', 'item_type' => 'goods', 'unit' => 'PCS'],
            ],
        ]);

        $this->assertSame(3, $result['total']);
        $this->assertSame(3, $result['valid']);
        $this->assertSame(0, $result['skipped']);
    }

    public function test_import_skips_invalid_rows_and_imports_valid_rows(): void
    {
        $service = new MasterDataImportService();

        $result = $service->import([
            'customers' => [
                ['code' => 'CUST-002', 'name' => 'PT Beta', 'customer_type' => 'company'],
                ['code' => '', 'name' => 'Invalid Customer', 'customer_type' => 'company'],
            ],
            'suppliers' => [
                ['supplier_code' => 'SUPP-002', 'name' => 'PT Supply', 'tax_type' => 'non_pkp'],
            ],
            'items' => [
                ['code' => 'ITEM-002', 'name' => 'Jasa B', 'item_type' => 'service', 'unit' => 'HOUR'],
            ],
        ]);

        $this->assertSame(4, $result['total']);
        $this->assertSame(3, $result['imported']);
        $this->assertSame(1, $result['skipped']);
        $this->assertDatabaseHas('customers', ['code' => 'CUST-002']);
        $this->assertDatabaseHas('suppliers', ['supplier_code' => 'SUPP-002']);
        $this->assertDatabaseHas('items', ['code' => 'ITEM-002']);
    }

    public function test_detects_duplicate_codes_per_entity(): void
    {
        $service = new MasterDataImportService();

        $result = $service->preview([
            'customers' => [
                ['code' => 'CUST-003', 'name' => 'A', 'customer_type' => 'company'],
                ['code' => 'CUST-003', 'name' => 'B', 'customer_type' => 'company'],
            ],
        ]);

        $this->assertSame(1, $result['skipped']);
        $this->assertStringContainsString('duplikat', strtolower($result['errors'][0]['errors'][0]));
    }

    public function test_detects_invalid_enum_values(): void
    {
        $service = new MasterDataImportService();

        $result = $service->preview([
            'items' => [
                ['code' => 'ITEM-004', 'name' => 'X', 'item_type' => 'invalid', 'unit' => 'PCS'],
            ],
        ]);

        $this->assertSame(1, $result['skipped']);
        $this->assertStringContainsString('item type', strtolower($result['errors'][0]['errors'][0]));
    }
}
