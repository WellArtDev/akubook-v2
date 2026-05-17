<?php

namespace Tests\Unit\Services;

use App\Services\ChartOfAccountsImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartOfAccountsImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_import_hierarchy_successfully(): void
    {
        $service = new ChartOfAccountsImportService();

        $result = $service->preview([
            'accounts' => [
                [
                    'code' => '1-1000',
                    'name' => 'Kas',
                    'type' => 'asset',
                    'is_header' => true,
                ],
                [
                    'code' => '1-1100',
                    'name' => 'Kas Kecil',
                    'type' => 'asset',
                    'parent_code' => '1-1000',
                    'is_header' => false,
                ],
            ],
        ]);

        $this->assertSame(2, $result['total']);
        $this->assertSame(2, $result['valid']);
        $this->assertSame(0, $result['skipped']);
    }

    public function test_import_skips_invalid_rows_and_still_imports_valid_rows(): void
    {
        $service = new ChartOfAccountsImportService();

        $result = $service->import([
            'accounts' => [
                [
                    'code' => '2-1000',
                    'name' => 'Hutang Usaha',
                    'type' => 'liability',
                ],
                [
                    'code' => '',
                    'name' => 'Invalid',
                    'type' => 'liability',
                ],
            ],
        ]);

        $this->assertSame(2, $result['total']);
        $this->assertSame(1, $result['imported']);
        $this->assertSame(1, $result['skipped']);
        $this->assertDatabaseHas('accounts', ['code' => '2-1000']);
    }

    public function test_import_rejects_duplicate_codes_in_payload(): void
    {
        $service = new ChartOfAccountsImportService();

        $result = $service->preview([
            'accounts' => [
                ['code' => '4-1000', 'name' => 'Pendapatan', 'type' => 'revenue'],
                ['code' => '4-1000', 'name' => 'Pendapatan Lain', 'type' => 'revenue'],
            ],
        ]);

        $this->assertSame(1, $result['skipped']);
        $this->assertStringContainsString('duplikat', strtolower($result['errors'][0]['errors'][0]));
    }

    public function test_import_detects_parent_cycle(): void
    {
        $service = new ChartOfAccountsImportService();

        $result = $service->preview([
            'accounts' => [
                ['code' => '5-1000', 'name' => 'Beban A', 'type' => 'expense', 'parent_code' => '5-2000'],
                ['code' => '5-2000', 'name' => 'Beban B', 'type' => 'expense', 'parent_code' => '5-1000'],
            ],
        ]);

        $this->assertSame(2, $result['skipped']);
    }
}
