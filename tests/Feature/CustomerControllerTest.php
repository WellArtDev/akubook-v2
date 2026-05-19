<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_it_displays_customers_index_page(): void
    {
        $this->actingAs($this->user);
        Customer::factory()->count(3)->create();

        $response = $this->get(route('customers.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Customers/Index'));
    }

    public function test_it_can_create_customer_with_contacts_and_addresses(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('customers.store'), $this->validCustomerData());

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', ['name' => 'Test Customer', 'category' => 'retail']);
        $this->assertDatabaseHas('customer_contacts', ['name' => 'John Doe', 'is_primary' => true]);
        $this->assertDatabaseHas('customer_addresses', ['city' => 'Jakarta', 'is_default' => true]);
    }

    public function test_it_can_update_customer_with_contacts_and_addresses(): void
    {
        $this->actingAs($this->user);
        $customer = Customer::factory()->create(['name' => 'Old Customer']);
        $contact = $customer->contacts()->create(['name' => 'Old Contact', 'phone' => '0811111111']);
        $address = $customer->addresses()->create(['address_type' => 'billing', 'street_address' => 'Old Street', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'country' => 'Indonesia']);
        $data = $this->validCustomerData(['name' => 'Updated Customer']);
        $data['contacts'][0]['id'] = $contact->id;
        $data['contacts'][0]['name'] = 'Updated Contact';
        $data['addresses'][0]['id'] = $address->id;
        $data['addresses'][0]['city'] = 'Surabaya';

        $response = $this->put(route('customers.update', $customer), $data);

        $response->assertRedirect(route('customers.index'));
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'name' => 'Updated Customer']);
        $this->assertDatabaseHas('customer_contacts', ['id' => $contact->id, 'name' => 'Updated Contact']);
        $this->assertDatabaseHas('customer_addresses', ['id' => $address->id, 'city' => 'Surabaya']);
    }

    public function test_it_soft_deletes_customer_without_transactions(): void
    {
        $this->actingAs($this->user);
        $customer = Customer::factory()->create();

        $response = $this->delete(route('customers.destroy', $customer));

        $response->assertRedirect(route('customers.index'));
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    public function test_it_cannot_delete_customer_with_sales_order(): void
    {
        $this->actingAs($this->user);
        $customer = Customer::factory()->create();
        SalesOrder::factory()->create(['customer_id' => $customer->id]);

        $response = $this->delete(route('customers.destroy', $customer));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('customers', ['id' => $customer->id, 'deleted_at' => null]);
    }

    public function test_it_validates_required_nested_customer_data(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('customers.store'), []);

        $response->assertSessionHasErrors(['name', 'category', 'tax_type', 'phone', 'credit_limit', 'payment_terms', 'contacts', 'addresses']);
    }

    public function test_customer_calculates_available_credit_and_status(): void
    {
        $customer = Customer::factory()->create([
            'credit_limit' => 10000000,
            'outstanding_balance' => 9000000,
        ]);

        $this->assertSame(1000000.0, $customer->available_credit);
        $this->assertSame('warning', $customer->credit_status);
    }

    private function validCustomerData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Customer',
            'category' => 'retail',
            'tax_id' => '01.234.567.8-901.000',
            'tax_type' => 'non_pkp',
            'phone' => '081234567890',
            'email' => 'customer@example.com',
            'website' => 'https://customer.example.com',
            'credit_limit' => 5000000,
            'payment_terms' => 30,
            'notes' => 'Test notes',
            'contacts' => [[
                'name' => 'John Doe',
                'position' => 'Manager',
                'phone' => '081234567891',
                'email' => 'john@example.com',
                'is_primary' => true,
            ]],
            'addresses' => [[
                'address_type' => 'both',
                'street_address' => 'Jl. Test No. 123',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'country' => 'Indonesia',
                'is_default' => true,
            ]],
        ], $overrides);
    }
}
