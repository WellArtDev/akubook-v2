<?php

namespace Database\Factories;

use App\Models\GoodsReceiptLine;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceLine;
use App\Models\PurchaseOrderLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseInvoiceLineFactory extends Factory
{
    protected $model = PurchaseInvoiceLine::class;

    public function definition(): array
    {
        return [
            'purchase_invoice_id' => PurchaseInvoice::factory(),
            'goods_receipt_line_id' => GoodsReceiptLine::factory(),
            'purchase_order_line_id' => PurchaseOrderLine::factory(),
            'line_number' => 1,
            'product_code' => 'RM-001',
            'product_name' => 'Raw Material',
            'ordered_quantity' => 10,
            'received_quantity' => 5,
            'previously_invoiced_quantity' => 0,
            'remaining_to_invoice_quantity' => 5,
            'invoice_quantity' => 1,
            'unit' => 'PCS',
            'unit_price' => 1000,
            'tax_percentage' => 11,
            'tax_amount' => 110,
            'line_total' => 1000,
        ];
    }
}
