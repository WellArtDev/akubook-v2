<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SupplierControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_it_displays_suppliers_index_page()
    {
        $this->actingAs($this->user);

        Supplier::factory()->count(3)->create(['created_by' => $this->user->id]);

        $response = $this->get(route('suppliers.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Suppliers/Index'));
    }

    public function test_it_can_create_a_supplier()
    {
        $this->actingAs($this->user);

        $supplierData = [
            'name' => 'Test Supplier',
            'category' => 'Raw Material',
            'tax_id' => '01.234.567.8-901.000',
            'tax_type' => 'pkp',
            'payment_terms' => 'Net 30',
            'phone' => '08123456789',
            'email' => 'test@supplier.com',
            'website' => 'https://supplier.com',
            'notes' => 'Test notes',
        ];

        $response = $this->post(route('suppliers.store'), $supplierData);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'name' => 'Test Supplier',
            'category' => 'Raw Material',
            'tax_type' => 'pkp',
        ]);
    }

    public function test_it_can_create_supplier_with_contacts()
    {
        $this->actingAs($this->user);

        $supplierData = [
            'name' => 'Test Supplier',
            'tax_type' => 'pkp',
            'contacts' => [
                [
                    'name' => 'John Doe',
                    'position' => 'Manager',
                    'phone' => '08123456789',
                    'email' => 'john@supplier.com',
                    'is_primary' => true,
                ],
            ],
        ];

        $response = $this->post(route('suppliers.store'), $supplierData);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('supplier_contacts', [
            'name' => 'John Doe',
            'phone' => '08123456789',
            'is_primary' => true,
        ]);
    }

    public function test_it_can_create_supplier_with_addresses()
    {
        $this->actingAs($this->user);

        $supplierData = [
            'name' => 'Test Supplier',
            'tax_type' => 'pkp',
            'addresses' => [
                [
                    'address_type' => 'billing',
                    'street_address' => 'Jl. Test No. 123',
                    'city' => 'Jakarta',
                    'province' => 'DKI Jakarta',
                    'postal_code' => '12345',
                    'country' => 'Indonesia',
                    'is_default' => true,
                ],
            ],
        ];

        $response = $this->post(route('suppliers.store'), $supplierData);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('supplier_addresses', [
            'street_address' => 'Jl. Test No. 123',
            'city' => 'Jakarta',
            'is_default' => true,
        ]);
    }

    public function test_it_can_update_a_supplier()
    {
        $this->actingAs($this->user);

        $supplier = Supplier::create([
            'name' => 'Old Name',
            'tax_type' => 'non_pkp',
            'created_by' => $this->user->id,
        ]);

        $updateData = [
            'name' => 'New Name',
            'category' => 'Service',
            'tax_type' => 'pkp',
            'payment_terms' => 'Net 45',
        ];

        $response = $this->put(route('suppliers.update', $supplier), $updateData);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'New Name',
            'category' => 'Service',
            'tax_type' => 'pkp',
        ]);
    }

    public function test_it_can_delete_a_supplier()
    {
        $this->actingAs($this->user);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'tax_type' => 'pkp',
            'created_by' => $this->user->id,
        ]);

        $response = $this->delete(route('suppliers.destroy', $supplier));

        $response->assertRedirect(route('suppliers.index'));
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_it_validates_required_fields()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('suppliers.store'), []);

        $response->assertSessionHasErrors(['name', 'tax_type']);
    }

    public function test_it_can_search_suppliers()
    {
        $this->actingAs($this->user);

        Supplier::create([
            'name' => 'ABC Supplier',
            'tax_type' => 'pkp',
            'created_by' => $this->user->id,
        ]);

        Supplier::create([
            'name' => 'XYZ Supplier',
            'tax_type' => 'non_pkp',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('suppliers.index', ['search' => 'ABC']));

        $response->assertStatus(200);
    }

    public function test_it_can_filter_by_category()
    {
        $this->actingAs($this->user);

        Supplier::create([
            'name' => 'Supplier 1',
            'category' => 'Raw Material',
            'tax_type' => 'pkp',
            'created_by' => $this->user->id,
        ]);

        Supplier::create([
            'name' => 'Supplier 2',
            'category' => 'Service',
            'tax_type' => 'pkp',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('suppliers.index', ['category' => 'Raw Material']));

        $response->assertStatus(200);
    }
}

