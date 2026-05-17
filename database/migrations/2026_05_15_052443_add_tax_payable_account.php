<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Account;

return new class extends Migration
{
    public function up(): void
    {
        // Add Tax Payable account to COA
        $liabilityParent = Account::where('code', '2-1000')->first();
        
        if ($liabilityParent) {
            Account::create([
                'code' => '2-1200',
                'name' => 'Hutang Pajak (PPN)',
                'type' => 'liability',
                'category' => 'current_liability',
                'parent_id' => $liabilityParent->id,
                'level' => 3,
                'is_header' => false,
                'is_active' => true,
                'description' => 'Hutang Pajak Pertambahan Nilai (PPN)',
                'balance' => 0,
            ]);
        }
    }

    public function down(): void
    {
        Account::where('code', '2-1200')->delete();
    }
};
