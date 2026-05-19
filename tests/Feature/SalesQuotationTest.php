<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Item;
use App\Models\SalesOrder;
use App\Models\SalesQuotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesQuotationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private CustomerContact $contact;
    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create(['is_active' => true, 'payment_terms' => 30]);
        $this->contact = CustomerContact::create(['customer_id' => $this->customer->id, 'name' => 'PIC Customer', 'position' => 'Purchasing', 'phone' => '08123456789', 'email' => 'contact@example.com', 'is_primary' => true]);
        $this->item = Item::factory()->create(['is_active' => true, 'selling_price' => 1000]);
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'quotation_date' => '2026-05-17',
            'valid_until' => '2026-06-17',
            'customer_id' => $this->customer->id,
            'customer_contact_id' => $this->contact->id,
            'reference' => 'REF-001',
            'payment_terms' => 'Net 30',
            'delivery_terms' => 'FOB',
            'notes' => 'Test',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'lines' => [[
                'item_id' => $this->item->id,
                'description' => 'Line 1',
                'quantity' => 10,
                'unit' => 'pcs',
                'unit_price' => 1000,
                'discount_percentage' => 0,
                'discount_amount' => 1000,
                'tax_percentage' => 11,
            ]],
        ], $extra);
    }

    public function test_index_create_show_page_loads(): void
    {
        $quotation = SalesQuotation::factory()->create(['customer_id' => $this->customer->id, 'sales_person_id' => $this->user->id, 'created_by' => $this->user->id]);

        $this->actingAs($this->user)->get(route('sales-quotations.index'))->assertOk();
        $this->actingAs($this->user)->get(route('sales-quotations.create'))->assertOk();
        $this->actingAs($this->user)->get(route('sales-quotations.show', $quotation))->assertOk();
    }

    public function test_store_calculates_totals(): void
    {
        $this->actingAs($this->user)->post(route('sales-quotations.store'), $this->payload())->assertRedirect();

        $quotation = SalesQuotation::with('lines')->first();
        $this->assertNotNull($quotation);
        $this->assertSame('draft', $quotation->status);
        $this->assertEquals(9000, (float) $quotation->subtotal);
        $this->assertEquals(900, (float) $quotation->discount_amount);
        $this->assertEquals(8100, (float) $quotation->subtotal_after_discount);
        $this->assertEquals(891, (float) $quotation->tax_amount);
        $this->assertEquals(8991, (float) $quotation->grand_total);
        $this->assertCount(1, $quotation->lines);
    }

    public function test_update_draft_and_block_update_non_draft(): void
    {
        $this->actingAs($this->user)->post(route('sales-quotations.store'), $this->payload());
        $quotation = SalesQuotation::first();

        $this->actingAs($this->user)->put(route('sales-quotations.update', $quotation), $this->payload(['reference' => 'REF-UPDATED']))->assertRedirect();
        $this->assertDatabaseHas('sales_quotations', ['id' => $quotation->id, 'reference' => 'REF-UPDATED']);

        $quotation->update(['status' => 'sent']);
        $this->actingAs($this->user)->put(route('sales-quotations.update', $quotation), $this->payload(['reference' => 'REF-BLOCKED']))->assertSessionHasErrors();
        $this->assertDatabaseMissing('sales_quotations', ['id' => $quotation->id, 'reference' => 'REF-BLOCKED']);
    }

    public function test_send_requires_email_then_sets_sent(): void
    {
        $this->actingAs($this->user)->post(route('sales-quotations.store'), $this->payload());
        $quotation = SalesQuotation::first();

        $quotation->customerContact()->update(['email' => null]);
        $this->actingAs($this->user)->post(route('sales-quotations.send', $quotation))->assertSessionHasErrors();

        $quotation->customerContact()->update(['email' => 'contact@example.com']);
        $this->actingAs($this->user)->post(route('sales-quotations.send', $quotation))->assertSessionHasNoErrors();
        $quotation->refresh();

        $this->assertSame('sent', $quotation->status);
        $this->assertNotNull($quotation->sent_at);
    }

    public function test_approve_and_reject_only_for_sent(): void
    {
        $this->actingAs($this->user)->post(route('sales-quotations.store'), $this->payload());
        $quotation = SalesQuotation::first();

        $this->actingAs($this->user)->post(route('sales-quotations.approve', $quotation))->assertSessionHasErrors();
        $quotation->update(['status' => 'sent']);
        $this->actingAs($this->user)->post(route('sales-quotations.approve', $quotation))->assertSessionHasNoErrors();
        $this->assertSame('approved', $quotation->fresh()->status);

        $quotation2 = SalesQuotation::factory()->create(['customer_id' => $this->customer->id, 'sales_person_id' => $this->user->id, 'created_by' => $this->user->id, 'status' => 'sent']);
        $this->actingAs($this->user)->post(route('sales-quotations.reject', $quotation2))->assertSessionHasNoErrors();
        $this->assertSame('rejected', $quotation2->fresh()->status);
    }

    public function test_revise_duplicate_convert_flows(): void
    {
        $this->actingAs($this->user)->post(route('sales-quotations.store'), $this->payload());
        $quotation = SalesQuotation::with('lines')->first();

        $quotation->update(['status' => 'sent']);
        $this->actingAs($this->user)->post(route('sales-quotations.revise', $quotation))->assertRedirect();
        $quotation->refresh();
        $revision = SalesQuotation::where('original_quotation_id', $quotation->id)->first();
        $this->assertNotNull($revision);
        $this->assertSame('draft', $revision->status);
        $this->assertStringContainsString('-R01', $revision->quotation_number);
        $this->assertSame('revised', $quotation->status);

        $this->actingAs($this->user)->post(route('sales-quotations.duplicate', $revision))->assertRedirect();
        $duplicate = SalesQuotation::latest('id')->first();
        $this->assertSame('draft', $duplicate->status);
        $this->assertNotSame($revision->quotation_number, $duplicate->quotation_number);

        $revision->update(['status' => 'approved', 'converted_to_sales_order_id' => null]);
        $this->actingAs($this->user)->post(route('sales-quotations.convert', $revision))->assertRedirect();
        $revision->refresh();
        $this->assertSame('converted', $revision->status);
        $this->assertNotNull($revision->converted_to_sales_order_id);
        $this->assertDatabaseHas('sales_orders', ['id' => $revision->converted_to_sales_order_id, 'sales_quotation_id' => $revision->id]);
        $this->assertInstanceOf(SalesOrder::class, SalesOrder::find($revision->converted_to_sales_order_id));
    }

    public function test_validation_requires_lines_and_valid_until_after_date(): void
    {
        $payload = $this->payload(['valid_until' => '2026-05-16', 'lines' => []]);
        $this->actingAs($this->user)->post(route('sales-quotations.store'), $payload)->assertSessionHasErrors(['valid_until', 'lines']);
    }
}
