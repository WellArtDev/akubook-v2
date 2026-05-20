<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GuardQueryPlanCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $path = base_path('_bmad-output/implementation-artifacts/performance-baselines/query-plan-baseline-v1.json');
        if (File::exists($path)) {
            File::delete($path);
        }

        parent::tearDown();
    }

    public function test_command_writes_baseline(): void
    {
        $this->seedMinimalData();

        $this->artisan('app:guard-query-plan', ['--write-baseline' => true])
            ->assertExitCode(0);

        $path = base_path('_bmad-output/implementation-artifacts/performance-baselines/query-plan-baseline-v1.json');
        $this->assertFileExists($path);

        $payload = json_decode((string) File::get($path), true);
        $this->assertArrayHasKey('plans', $payload);
        $this->assertArrayHasKey('sales_order_status_aggregate', $payload['plans']);
    }

    public function test_command_fails_on_simulated_regression(): void
    {
        $this->seedMinimalData();

        $this->artisan('app:guard-query-plan', ['--write-baseline' => true])
            ->assertExitCode(0);

        $this->artisan('app:guard-query-plan', ['--simulate-regression' => true])
            ->assertExitCode(1);
    }

    private function seedMinimalData(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $supplier = Supplier::factory()->create();

        SalesOrder::query()->create([
            'so_number' => 'SO-'.now()->year.'-0099',
            'so_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'sales_person_id' => $user->id,
            'status' => 'approved',
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 100000,
            'total_amount' => 100000,
            'created_by' => $user->id,
        ]);

        PurchaseOrder::query()->create([
            'po_number' => 'PO-'.now()->year.'-0099',
            'po_date' => now()->toDateString(),
            'supplier_id' => $supplier->id,
            'status' => 'approved',
            'subtotal' => 90000,
            'tax_amount' => 0,
            'grand_total' => 90000,
            'created_by' => $user->id,
        ]);

        AuditLog::factory()->create([
            'event_key' => 'workflow.enforcement.evaluated',
            'occurred_at' => now(),
        ]);
    }
}
