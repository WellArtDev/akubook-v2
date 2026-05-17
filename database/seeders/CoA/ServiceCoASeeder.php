<?php

namespace Database\Seeders\CoA;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceCoASeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $accounts = $this->getAccounts();
            $idMap = [];
            
            foreach ($accounts as $index => $account) {
                $this->validateAccount($account);
                
                // Resolve parent_id from idMap if not null
                if ($account['parent_id'] !== null) {
                    $account['parent_id'] = $idMap[$account['parent_id']] ?? null;
                }
                
                $created = Account::create($account);
                $idMap[$index + 1] = $created->id; // Store ID for future reference
            }
        });
    }

    private function getAccounts(): array
    {
        return [
            // ========== 1-0000: ASET ==========
            ['code' => '1-0000', 'name' => 'ASET', 'type' => 'asset', 'category' => null, 'parent_id' => null, 'level' => 1, 'is_header' => true, 'is_active' => true],
            
            // 1-1000: Aset Lancar
            ['code' => '1-1000', 'name' => 'Aset Lancar', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 1, 'level' => 2, 'is_header' => true, 'is_active' => true],
            ['code' => '1-1100', 'name' => 'Kas', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-1110', 'name' => 'Kas Kecil', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-1200', 'name' => 'Bank', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-1300', 'name' => 'Piutang Usaha', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-1310', 'name' => 'Cadangan Kerugian Piutang', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            
            // SERVICE SPECIFIC: Unbilled Revenue & WIP
            ['code' => '1-1500', 'name' => 'Piutang Belum Ditagih', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-1600', 'name' => 'Pekerjaan Dalam Proses', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            
            ['code' => '1-1700', 'name' => 'Uang Muka', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-1800', 'name' => 'Biaya Dibayar Dimuka', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            
            // 1-2000: Aset Tetap
            ['code' => '1-2000', 'name' => 'Aset Tetap', 'type' => 'asset', 'category' => 'fixed_asset', 'parent_id' => 1, 'level' => 2, 'is_header' => true, 'is_active' => true],
            ['code' => '1-2100', 'name' => 'Tanah', 'type' => 'asset', 'category' => 'fixed_asset', 'parent_id' => 12, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-2200', 'name' => 'Bangunan', 'type' => 'asset', 'category' => 'fixed_asset', 'parent_id' => 12, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-2210', 'name' => 'Akumulasi Penyusutan Bangunan', 'type' => 'asset', 'category' => 'fixed_asset', 'parent_id' => 12, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-2300', 'name' => 'Kendaraan', 'type' => 'asset', 'category' => 'fixed_asset', 'parent_id' => 12, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-2310', 'name' => 'Akumulasi Penyusutan Kendaraan', 'type' => 'asset', 'category' => 'fixed_asset', 'parent_id' => 12, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-2400', 'name' => 'Peralatan Kantor', 'type' => 'asset', 'category' => 'fixed_asset', 'parent_id' => 12, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-2410', 'name' => 'Akumulasi Penyusutan Peralatan', 'type' => 'asset', 'category' => 'fixed_asset', 'parent_id' => 12, 'level' => 3, 'is_header' => false, 'is_active' => true],
            
            // ========== 2-0000: KEWAJIBAN ==========
            ['code' => '2-0000', 'name' => 'KEWAJIBAN', 'type' => 'liability', 'category' => null, 'parent_id' => null, 'level' => 1, 'is_header' => true, 'is_active' => true],
            
            // 2-1000: Kewajiban Lancar
            ['code' => '2-1000', 'name' => 'Kewajiban Lancar', 'type' => 'liability', 'category' => 'current_liability', 'parent_id' => 20, 'level' => 2, 'is_header' => true, 'is_active' => true],
            ['code' => '2-1100', 'name' => 'Hutang Usaha', 'type' => 'liability', 'category' => 'current_liability', 'parent_id' => 21, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '2-1200', 'name' => 'Hutang Pajak', 'type' => 'liability', 'category' => 'current_liability', 'parent_id' => 21, 'level' => 3, 'is_header' => false, 'is_active' => true],
            
            // SERVICE SPECIFIC: Deferred Revenue
            ['code' => '2-1300', 'name' => 'Pendapatan Diterima Dimuka', 'type' => 'liability', 'category' => 'current_liability', 'parent_id' => 21, 'level' => 3, 'is_header' => false, 'is_active' => true],
            
            ['code' => '2-1400', 'name' => 'Hutang Gaji', 'type' => 'liability', 'category' => 'current_liability', 'parent_id' => 21, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '2-1500', 'name' => 'Hutang Lain-lain', 'type' => 'liability', 'category' => 'current_liability', 'parent_id' => 21, 'level' => 3, 'is_header' => false, 'is_active' => true],
            
            // 2-2000: Kewajiban Jangka Panjang
            ['code' => '2-2000', 'name' => 'Kewajiban Jangka Panjang', 'type' => 'liability', 'category' => 'long_term_liability', 'parent_id' => 20, 'level' => 2, 'is_header' => true, 'is_active' => true],
            ['code' => '2-2100', 'name' => 'Hutang Bank', 'type' => 'liability', 'category' => 'long_term_liability', 'parent_id' => 27, 'level' => 3, 'is_header' => false, 'is_active' => true],
            
            // ========== 3-0000: EKUITAS ==========
            ['code' => '3-0000', 'name' => 'EKUITAS', 'type' => 'equity', 'category' => 'equity', 'parent_id' => null, 'level' => 1, 'is_header' => true, 'is_active' => true],
            ['code' => '3-1000', 'name' => 'Modal', 'type' => 'equity', 'category' => 'equity', 'parent_id' => 29, 'level' => 2, 'is_header' => false, 'is_active' => true],
            ['code' => '3-2000', 'name' => 'Laba Ditahan', 'type' => 'equity', 'category' => 'equity', 'parent_id' => 29, 'level' => 2, 'is_header' => false, 'is_active' => true],
            ['code' => '3-3000', 'name' => 'Laba Tahun Berjalan', 'type' => 'equity', 'category' => 'equity', 'parent_id' => 29, 'level' => 2, 'is_header' => false, 'is_active' => true],
            
            // ========== 4-0000: PENDAPATAN ==========
            ['code' => '4-0000', 'name' => 'PENDAPATAN', 'type' => 'revenue', 'category' => null, 'parent_id' => null, 'level' => 1, 'is_header' => true, 'is_active' => true],
            
            // SERVICE SPECIFIC: Pendapatan Jasa dengan sub-kategori
            ['code' => '4-1000', 'name' => 'Pendapatan Jasa Konsultasi', 'type' => 'revenue', 'category' => 'operating_revenue', 'parent_id' => 33, 'level' => 2, 'is_header' => false, 'is_active' => true],
            ['code' => '4-1100', 'name' => 'Pendapatan Jasa Maintenance', 'type' => 'revenue', 'category' => 'operating_revenue', 'parent_id' => 33, 'level' => 2, 'is_header' => false, 'is_active' => true],
            ['code' => '4-1200', 'name' => 'Professional Fees', 'type' => 'revenue', 'category' => 'operating_revenue', 'parent_id' => 33, 'level' => 2, 'is_header' => false, 'is_active' => true],
            
            ['code' => '4-2000', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue', 'category' => 'other_revenue', 'parent_id' => 33, 'level' => 2, 'is_header' => false, 'is_active' => true],
            
            // ========== 5-0000: BEBAN ==========
            ['code' => '5-0000', 'name' => 'BEBAN', 'type' => 'expense', 'category' => null, 'parent_id' => null, 'level' => 1, 'is_header' => true, 'is_active' => true],
            
            // SERVICE SPECIFIC: Project Costs
            ['code' => '5-1000', 'name' => 'Biaya Proyek Langsung', 'type' => 'expense', 'category' => 'operating_expense', 'parent_id' => 38, 'level' => 2, 'is_header' => false, 'is_active' => true],
            
            // 5-2000: Beban Operasional
            ['code' => '5-2000', 'name' => 'Beban Operasional', 'type' => 'expense', 'category' => 'operating_expense', 'parent_id' => 38, 'level' => 2, 'is_header' => true, 'is_active' => true],
            ['code' => '5-2100', 'name' => 'Beban Gaji', 'type' => 'expense', 'category' => 'operating_expense', 'parent_id' => 40, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '5-2200', 'name' => 'Beban Sewa', 'type' => 'expense', 'category' => 'operating_expense', 'parent_id' => 40, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '5-2300', 'name' => 'Beban Listrik & Air', 'type' => 'expense', 'category' => 'operating_expense', 'parent_id' => 40, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '5-2400', 'name' => 'Beban Telepon & Internet', 'type' => 'expense', 'category' => 'operating_expense', 'parent_id' => 40, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '5-2500', 'name' => 'Beban Penyusutan', 'type' => 'expense', 'category' => 'operating_expense', 'parent_id' => 40, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '5-2600', 'name' => 'Beban Administrasi', 'type' => 'expense', 'category' => 'operating_expense', 'parent_id' => 40, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '5-2700', 'name' => 'Beban Pemeliharaan', 'type' => 'expense', 'category' => 'operating_expense', 'parent_id' => 40, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '5-2800', 'name' => 'Beban Pelatihan & Pengembangan', 'type' => 'expense', 'category' => 'operating_expense', 'parent_id' => 40, 'level' => 3, 'is_header' => false, 'is_active' => true],
            
            // 5-3000: Beban Lain-lain
            ['code' => '5-3000', 'name' => 'Beban Lain-lain', 'type' => 'expense', 'category' => 'other_expense', 'parent_id' => 38, 'level' => 2, 'is_header' => false, 'is_active' => true],
        ];
    }

    private function validateAccount(array $account): void
    {
        $required = ['code', 'name', 'type', 'level', 'is_header', 'is_active'];
        foreach ($required as $field) {
            if (!isset($account[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }
    }
}
