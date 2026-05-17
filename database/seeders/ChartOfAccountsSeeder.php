<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Assets
            ['code' => '1-0000', 'name' => 'ASET', 'type' => 'asset', 'category' => null, 'parent_id' => null, 'level' => 1, 'is_header' => true],
            ['code' => '1-1000', 'name' => 'Aset Lancar', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 1, 'level' => 2, 'is_header' => true],
            ['code' => '1-1100', 'name' => 'Kas & Bank', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false],
            ['code' => '1-1200', 'name' => 'Piutang Usaha', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false],
            ['code' => '1-1300', 'name' => 'Persediaan', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false],
            
            // Liabilities
            ['code' => '2-0000', 'name' => 'KEWAJIBAN', 'type' => 'liability', 'category' => null, 'parent_id' => null, 'level' => 1, 'is_header' => true],
            ['code' => '2-1000', 'name' => 'Kewajiban Lancar', 'type' => 'liability', 'category' => 'current_liability', 'parent_id' => 6, 'level' => 2, 'is_header' => true],
            ['code' => '2-1100', 'name' => 'Hutang Usaha', 'type' => 'liability', 'category' => 'current_liability', 'parent_id' => 7, 'level' => 3, 'is_header' => false],
            
            // Equity
            ['code' => '3-0000', 'name' => 'EKUITAS', 'type' => 'equity', 'category' => 'equity', 'parent_id' => null, 'level' => 1, 'is_header' => true],
            ['code' => '3-1000', 'name' => 'Modal', 'type' => 'equity', 'category' => 'equity', 'parent_id' => 9, 'level' => 2, 'is_header' => false],
            ['code' => '3-2000', 'name' => 'Laba Ditahan', 'type' => 'equity', 'category' => 'equity', 'parent_id' => 9, 'level' => 2, 'is_header' => false],
            
            // Revenue
            ['code' => '4-0000', 'name' => 'PENDAPATAN', 'type' => 'revenue', 'category' => null, 'parent_id' => null, 'level' => 1, 'is_header' => true],
            ['code' => '4-1000', 'name' => 'Pendapatan Usaha', 'type' => 'revenue', 'category' => 'operating_revenue', 'parent_id' => 12, 'level' => 2, 'is_header' => false],
            
            // Expenses
            ['code' => '5-0000', 'name' => 'BEBAN', 'type' => 'expense', 'category' => null, 'parent_id' => null, 'level' => 1, 'is_header' => true],
            ['code' => '5-1000', 'name' => 'Beban Pokok Penjualan', 'type' => 'expense', 'category' => 'operating_expense', 'parent_id' => 14, 'level' => 2, 'is_header' => false],
            ['code' => '5-2000', 'name' => 'Beban Operasional', 'type' => 'expense', 'category' => 'operating_expense', 'parent_id' => 14, 'level' => 2, 'is_header' => false],
        ];

        foreach ($accounts as $account) {
            Account::create($account);
        }
    }
}
