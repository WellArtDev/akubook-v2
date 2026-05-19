<?php

namespace Tests\Unit;

use App\Services\JournalService;
use BadMethodCallException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_generate_from_purchase_invoice_throws_not_implemented_exception()
    {
        $service = new JournalService();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('TODO: Implement after Epic 9 (Purchase module).');

        $service->generateFromPurchaseInvoice(1);
    }
}
