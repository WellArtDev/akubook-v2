<?php

namespace Database\Factories;

use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesInvoice>
 */
class SalesInvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 1000, 100000);
        $taxAmount = $subtotal * 0.11;
        $grandTotal = $subtotal + $taxAmount;

        return [
            'invoice_number' => SalesInvoice::generateInvoiceNumber(),
            'invoice_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+2 months'),
            'sales_order_id' => SalesOrder::factory(),
            'customer_id' => Customer::factory(),
            'billing_address' => fake()->address(),
            'tax_invoice_number' => null,
            'payment_terms' => fake()->randomElement(['Net 0', 'Net 7', 'Net 14', 'Net 30', 'Net 45', 'Net 60']),
            'reference' => fake()->optional()->bothify('REF-####'),
            'notes' => fake()->optional()->sentence(),
            'status' => 'draft',
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'amount_paid' => 0,
            'amount_due' => $grandTotal,
            'journal_entry_id' => null,
            'sent_at' => null,
            'cancelled_by' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}
