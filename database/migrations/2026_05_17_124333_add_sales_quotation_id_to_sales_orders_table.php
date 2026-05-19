<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sales_orders', 'sales_quotation_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->foreignId('sales_quotation_id')->nullable()->constrained('sales_quotations')->onDelete('set null');
            });
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement("CREATE TEMP TABLE __sales_orders_tmp AS SELECT * FROM sales_orders");
            DB::statement('DROP TABLE sales_orders');
            DB::statement('CREATE TABLE sales_orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                so_number VARCHAR NOT NULL,
                so_date DATE NOT NULL,
                customer_id INTEGER NOT NULL,
                branch_id INTEGER,
                customer_po_number VARCHAR,
                sales_person_id INTEGER NOT NULL,
                payment_terms VARCHAR,
                delivery_terms VARCHAR,
                delivery_address_id INTEGER,
                requested_delivery_date DATE,
                notes TEXT,
                status VARCHAR CHECK(status IN (\'draft\', \'pending_approval\', \'approved\', \'in_progress\', \'completed\', \'cancelled\')) NOT NULL DEFAULT \'draft\',
                subtotal NUMERIC NOT NULL DEFAULT 0,
                discount_amount NUMERIC NOT NULL DEFAULT 0,
                tax_amount NUMERIC NOT NULL DEFAULT 0,
                grand_total NUMERIC NOT NULL DEFAULT 0,
                total_amount NUMERIC NOT NULL DEFAULT 0,
                approval_required TINYINT(1) NOT NULL DEFAULT 0,
                approved_by INTEGER,
                approved_at DATETIME,
                credit_check_passed TINYINT(1) NOT NULL DEFAULT 1,
                credit_check_notes TEXT,
                created_by INTEGER NOT NULL,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME,
                sales_quotation_id INTEGER,
                FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
                FOREIGN KEY(branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
                FOREIGN KEY(sales_person_id) REFERENCES users(id) ON DELETE RESTRICT,
                FOREIGN KEY(delivery_address_id) REFERENCES branches(id) ON DELETE RESTRICT,
                FOREIGN KEY(approved_by) REFERENCES users(id) ON DELETE RESTRICT,
                FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE RESTRICT,
                FOREIGN KEY(sales_quotation_id) REFERENCES sales_quotations(id) ON DELETE SET NULL
            )');
            DB::statement('INSERT INTO sales_orders SELECT id, so_number, so_date, customer_id, branch_id, customer_po_number, sales_person_id, payment_terms, delivery_terms, delivery_address_id, requested_delivery_date, notes, status, subtotal, discount_amount, tax_amount, grand_total, total_amount, approval_required, approved_by, approved_at, credit_check_passed, credit_check_notes, created_by, created_at, updated_at, deleted_at, sales_quotation_id FROM __sales_orders_tmp');
            DB::statement('DROP TABLE __sales_orders_tmp');
            DB::statement('CREATE INDEX sales_orders_so_date_status_index ON sales_orders (so_date, status)');
            DB::statement('CREATE INDEX sales_orders_customer_id_index ON sales_orders (customer_id)');
            DB::statement('CREATE UNIQUE INDEX sales_orders_so_number_unique ON sales_orders (so_number)');
        }
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sales_quotation_id');
        });
    }
};
