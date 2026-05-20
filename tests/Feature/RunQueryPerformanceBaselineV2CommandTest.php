<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ComplianceExportPack;
use App\Models\CustomerPayment;
use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class RunQueryPerformanceBaselineV2CommandTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('_bmad-output/implementation-artifacts/performance-baselines'));

        parent::tearDown();
    }

    public function test_command_saves_query_baseline_v2_artifact(): void
    {
        $dateFrom = now()->subDay()->toDateString();
        $dateTo = now()->toDateString();

        $customer = Customer::factory()->create();
        $supplier = Supplier::factory()->create();

        SalesOrder::query()->create([
            'so_number' => 'SO-'.now()->year.'-0001',
            'so_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'sales_person_id' => $this->user->id,
            'status' => 'approved',
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 100000,
            'total_amount' => 100000,
            'created_by' => $this->user->id,
        ]);

        PurchaseOrder::query()->create([
            'po_number' => 'PO-'.now()->year.'-0001',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplier->id,
            'status' => 'approved',
            'subtotal' => 90000,
            'tax_amount' => 0,
            'grand_total' => 90000,
            'created_by' => $this->user->id,
        ]);

        AuditLog::factory()->create([
            'event_key' => 'workflow.enforcement.evaluated',
            'occurred_at' => now(),
        ]);

        ComplianceExportPack::query()->create([
            'pack_number' => 'CEP-'.now()->year.'-0001',
            'period_start' => now()->toDateString(),
            'period_end' => now()->toDateString(),
            'status' => 'generated',
            'record_counts' => ['test' => 1],
            'metadata' => ['test' => true],
            'payload_json' => '{}',
            'generated_by' => $this->user->id,
            'generated_at' => now(),
        ]);

        CustomerPayment::query()->create([
            'payment_number' => 'PAY-'.now()->year.'-0001',
            'payment_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'total_amount' => 50000,
            'allocated_amount' => 0,
            'unapplied_amount' => 50000,
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        $this->artisan('app:benchmark-query-baseline-v2', [
            '--date-from' => $dateFrom,
            '--date-to' => $dateTo,
            '--fast-ms' => 10,
            '--slow-ms' => 20,
            '--critical-ms' => 30,
        ])->assertExitCode(0);

        $path = base_path("_bmad-output/implementation-artifacts/performance-baselines/query-baseline-v2-{$dateFrom}_{$dateTo}.json");
        $this->assertFileExists($path);

        $payload = json_decode(File::get($path), true);

        $this->assertSame($dateFrom, $payload['period']['date_from']);
        $this->assertSame($dateTo, $payload['period']['date_to']);
        $this->assertSame(10, $payload['thresholds']['fast_ms']);
        $this->assertArrayHasKey('sales', $payload['benchmarks']);
        $this->assertArrayHasKey('purchase', $payload['benchmarks']);
        $this->assertArrayHasKey('governance', $payload['benchmarks']);
        $this->assertNotEmpty($payload['recommendations']);
    }

    public function test_command_rejects_invalid_threshold_order(): void
    {
        $this->artisan('app:benchmark-query-baseline-v2', [
            '--fast-ms' => 100,
            '--slow-ms' => 50,
            '--critical-ms' => 10,
        ])->assertExitCode(1);
    }
}
