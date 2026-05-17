<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_supplier_code_automatically()
    {
        $user = User::factory()->create();
        
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'tax_type' => 'pkp',
            'created_by' => $user->id,
        ]);

        $this->assertNotNull($supplier->supplier_code);
        $this->assertStringStartsWith('SUPP-' . date('Y') . '-', $supplier->supplier_code);
    }

    public function test_it_generates_sequential_supplier_codes()
    {
        $user = User::factory()->create();
        
        $supplier1 = Supplier::create([
            'name' => 'Supplier 1',
            'tax_type' => 'pkp',
            'created_by' => $user->id,
        ]);

        $supplier2 = Supplier::create([
            'name' => 'Supplier 2',
            'tax_type' => 'non_pkp',
            'created_by' => $user->id,
        ]);

        $code1Number = (int) substr($supplier1->supplier_code, -4);
        $code2Number = (int) substr($supplier2->supplier_code, -4);

        $this->assertEquals($code1Number + 1, $code2Number);
    }

    public function test_it_has_contacts_relationship()
    {
        $user = User::factory()->create();
        
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'tax_type' => 'pkp',
            'created_by' => $user->id,
        ]);

        $supplier->contacts()->create([
            'name' => 'John Doe',
            'phone' => '08123456789',
            'is_primary' => true,
        ]);

        $this->assertCount(1, $supplier->contacts);
        $this->assertEquals('John Doe', $supplier->contacts->first()->name);
    }

    public function test_it_has_addresses_relationship()
    {
        $user = User::factory()->create();
        
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'tax_type' => 'pkp',
            'created_by' => $user->id,
        ]);

        $supplier->addresses()->create([
            'address_type' => 'billing',
            'street_address' => 'Jl. Test No. 123',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'is_default' => true,
        ]);

        $this->assertCount(1, $supplier->addresses);
        $this->assertEquals('Jakarta', $supplier->addresses->first()->city);
    }

    public function test_it_gets_primary_contact()
    {
        $user = User::factory()->create();
        
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'tax_type' => 'pkp',
            'created_by' => $user->id,
        ]);

        $supplier->contacts()->create([
            'name' => 'John Doe',
            'phone' => '08123456789',
            'is_primary' => false,
        ]);

        $primaryContact = $supplier->contacts()->create([
            'name' => 'Jane Doe',
            'phone' => '08987654321',
            'is_primary' => true,
        ]);

        $this->assertEquals($primaryContact->id, $supplier->primaryContact()->id);
    }

    public function test_it_gets_default_address()
    {
        $user = User::factory()->create();
        
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'tax_type' => 'pkp',
            'created_by' => $user->id,
        ]);

        $supplier->addresses()->create([
            'address_type' => 'shipping',
            'street_address' => 'Jl. Test No. 456',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'is_default' => false,
        ]);

        $defaultAddress = $supplier->addresses()->create([
            'address_type' => 'billing',
            'street_address' => 'Jl. Test No. 123',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'is_default' => true,
        ]);

        $this->assertEquals($defaultAddress->id, $supplier->defaultAddress()->id);
    }

    public function test_it_soft_deletes_supplier()
    {
        $user = User::factory()->create();
        
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'tax_type' => 'pkp',
            'created_by' => $user->id,
        ]);

        $supplier->delete();

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }
}
