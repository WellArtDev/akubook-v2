# Story 4.2: Industry-Specific CoA Templates

**Epic:** 4 - Chart of Accounts & Fiscal Periods  
**Story ID:** 4.2  
**Story Key:** 4-2-industry-specific-coa-templates  
**Status:** done  
**Created:** 2026-05-14  
**Started:** 2026-05-14  
**Completed:** 2026-05-14  
**Priority:** Medium

---

## User Story

**Sebagai** Finance Admin  
**Saya ingin** memilih template Chart of Accounts sesuai industri bisnis saya  
**Sehingga** saya tidak perlu membuat CoA dari nol dan langsung dapat struktur akun yang sesuai dengan jenis bisnis

---

## Business Context

Medium enterprises di Indonesia beroperasi di berbagai industri dengan kebutuhan akuntansi yang berbeda:
- **Distributor**: Butuh akun untuk inventory, freight, sales commission
- **Retail/Toko**: Butuh akun untuk point of sale, cash register, retail expenses
- **Service/Jasa**: Butuh akun untuk service revenue, professional fees, project costs
- **General/Umum**: Template dasar untuk bisnis yang tidak masuk kategori spesifik

Setiap industri memiliki pola transaksi dan kebutuhan pelaporan yang berbeda. Template CoA yang sesuai industri akan:
1. Mempercepat setup awal sistem
2. Memastikan struktur akun sesuai best practice industri
3. Mengurangi kesalahan konfigurasi
4. Memudahkan migrasi dari sistem lain (terutama Accurate)

---

## Acceptance Criteria

### AC1: Artisan Command untuk Generate Template
```bash
php artisan coa:generate {industry}
```
- ✅ Command menerima parameter industry: general, distributor, retail, service
- ✅ Command menghapus CoA existing (dengan konfirmasi) sebelum generate
- ✅ Command men-seed CoA sesuai template yang dipilih
- ✅ Command menampilkan summary: jumlah akun ter-create, struktur level
- ✅ Command memiliki flag --force untuk skip konfirmasi (untuk automation)

### AC2: Template General (Umum)
- ✅ Struktur 5 kategori utama: Asset, Liability, Equity, Revenue, Expense
- ✅ Minimal 30 akun detail yang umum digunakan
- ✅ Hierarki 3 level: Header → Sub-header → Detail
- ✅ Kode akun format: X-XXXX (1-1100, 2-1200, dst)
- ✅ Nama akun dalam Bahasa Indonesia

### AC3: Template Distributor
- ✅ Semua akun dari template General
- ✅ Tambahan akun khusus distributor:
  - Persediaan Barang Dagangan (dengan sub-kategori)
  - Biaya Pengiriman/Freight
  - Komisi Penjualan
  - Retur Penjualan & Pembelian
  - Diskon Penjualan & Pembelian
- ✅ Minimal 45 akun detail

### AC4: Template Retail
- ✅ Semua akun dari template General
- ✅ Tambahan akun khusus retail:
  - Kas Toko/Cash Register
  - Persediaan Toko
  - Biaya Sewa Toko
  - Biaya Promosi & Marketing
  - Biaya Packaging
  - Selisih Kas (Cash Over/Short)
- ✅ Minimal 40 akun detail

### AC5: Template Service
- ✅ Semua akun dari template General
- ✅ Tambahan akun khusus service:
  - Pendapatan Jasa (dengan sub-kategori)
  - Professional Fees
  - Project Costs
  - Unbilled Revenue
  - Deferred Revenue
  - Work in Progress
- ✅ Minimal 35 akun detail

### AC6: Seeder Classes
- ✅ Seeder terpisah untuk setiap template:
  - `GeneralCoASeeder.php`
  - `DistributorCoASeeder.php`
  - `RetailCoASeeder.php`
  - `ServiceCoASeeder.php`
- ✅ Seeder dapat dipanggil independent atau via command
- ✅ Seeder memiliki data validation sebelum insert
- ✅ Seeder menggunakan DB transaction untuk atomicity

### AC7: Documentation
- ✅ README.md di `database/seeders/coa/` menjelaskan:
  - Cara menggunakan command
  - Daftar template available
  - Struktur akun per template
  - Cara menambah template baru
- ✅ Inline comments di seeder menjelaskan kategori akun

---

## Technical Requirements

### File Structure
```
database/
  seeders/
    coa/
      README.md
      GeneralCoASeeder.php
      DistributorCoASeeder.php
      RetailCoASeeder.php
      ServiceCoASeeder.php
app/
  Console/
    Commands/
      GenerateCoACommand.php
```

### Command Implementation
```php
// app/Console/Commands/GenerateCoACommand.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\CoA\{GeneralCoASeeder, DistributorCoASeeder, RetailCoASeeder, ServiceCoASeeder};
use App\Models\Account;

class GenerateCoACommand extends Command
{
    protected $signature = 'coa:generate {industry : Industry type (general|distributor|retail|service)} {--force : Skip confirmation}';
    protected $description = 'Generate Chart of Accounts template for specific industry';

    public function handle()
    {
        $industry = $this->argument('industry');
        $force = $this->option('force');

        // Validate industry
        $validIndustries = ['general', 'distributor', 'retail', 'service'];
        if (!in_array($industry, $validIndustries)) {
            $this->error("Invalid industry. Choose: " . implode(', ', $validIndustries));
            return 1;
        }

        // Check existing accounts
        $existingCount = Account::count();
        if ($existingCount > 0 && !$force) {
            if (!$this->confirm("Found {$existingCount} existing accounts. Delete and regenerate?")) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Delete existing accounts
        if ($existingCount > 0) {
            Account::query()->forceDelete();
            $this->info("Deleted {$existingCount} existing accounts.");
        }

        // Run appropriate seeder
        $seederClass = match($industry) {
            'general' => GeneralCoASeeder::class,
            'distributor' => DistributorCoASeeder::class,
            'retail' => RetailCoASeeder::class,
            'service' => ServiceCoASeeder::class,
        };

        $this->call('db:seed', ['--class' => $seederClass]);

        // Display summary
        $newCount = Account::count();
        $headerCount = Account::headers()->count();
        $detailCount = Account::details()->count();

        $this->info("\n✅ Chart of Accounts generated successfully!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Accounts', $newCount],
                ['Header Accounts', $headerCount],
                ['Detail Accounts', $detailCount],
                ['Industry Template', ucfirst($industry)],
            ]
        );

        return 0;
    }
}
```

### Seeder Pattern
```php
// database/seeders/coa/GeneralCoASeeder.php
namespace Database\Seeders\CoA;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeneralCoASeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $accounts = $this->getAccounts();
            
            foreach ($accounts as $account) {
                $this->validateAccount($account);
                Account::create($account);
            }
        });
    }

    private function getAccounts(): array
    {
        return [
            // 1-0000: ASET (Header Level 1)
            ['code' => '1-0000', 'name' => 'ASET', 'type' => 'asset', 'category' => null, 'parent_id' => null, 'level' => 1, 'is_header' => true, 'is_active' => true],
            
            // 1-1000: Aset Lancar (Header Level 2)
            ['code' => '1-1000', 'name' => 'Aset Lancar', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 1, 'level' => 2, 'is_header' => true, 'is_active' => true],
            
            // Detail accounts under Aset Lancar
            ['code' => '1-1100', 'name' => 'Kas', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-1110', 'name' => 'Kas Kecil', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-1200', 'name' => 'Bank', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-1300', 'name' => 'Piutang Usaha', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            ['code' => '1-1400', 'name' => 'Persediaan', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 2, 'level' => 3, 'is_header' => false, 'is_active' => true],
            
            // ... (continue with full structure)
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
```

---

## Developer Context

### Existing Infrastructure

**Models:**
- `Account` model sudah ada di `app/Models/Account.php`
- Fields: code, name, type, category, parent_id, level, is_header, is_active, description, balance
- Relationships: parent(), children()
- Scopes: active(), byType(), headers(), details()

**Seeders:**
- `ChartOfAccountsSeeder.php` sudah ada dengan 16 akun basic
- Pattern sudah established: array of accounts → loop → create
- Menggunakan parent_id untuk hierarki (bukan nested set)

**Database:**
- Table `accounts` sudah ada dengan semua fields required
- SoftDeletes enabled
- Auditable trait active

### Implementation Notes

1. **Command Location**: Buat di `app/Console/Commands/GenerateCoACommand.php`
2. **Seeder Organization**: Buat folder `database/seeders/coa/` untuk organize templates
3. **Parent ID Resolution**: 
   - Seeder harus insert secara berurutan (parent dulu, child kemudian)
   - Parent_id menggunakan ID auto-increment, bukan hardcoded
   - Alternatif: gunakan code-based lookup untuk parent_id
4. **Transaction Safety**: Wrap semua insert dalam DB::transaction()
5. **Validation**: Validate required fields sebelum insert
6. **Testing**: Test setiap template dengan fresh database

### Code Pattern dari Existing Seeder

```php
// Existing pattern di ChartOfAccountsSeeder.php
$accounts = [
    ['code' => '1-0000', 'name' => 'ASET', 'type' => 'asset', 'category' => null, 'parent_id' => null, 'level' => 1, 'is_header' => true],
    ['code' => '1-1000', 'name' => 'Aset Lancar', 'type' => 'asset', 'category' => 'current_asset', 'parent_id' => 1, 'level' => 2, 'is_header' => true],
];

foreach ($accounts as $account) {
    Account::create($account);
}
```

**Improvement untuk Template Seeders:**
- Tambahkan `is_active => true` default
- Tambahkan validation method
- Gunakan DB::transaction()
- Tambahkan inline comments untuk kategori

### Account Type & Category Reference

**Types:**
- `asset`: Aset
- `liability`: Kewajiban
- `equity`: Ekuitas
- `revenue`: Pendapatan
- `expense`: Beban

**Categories:**
- `current_asset`: Aset Lancar
- `fixed_asset`: Aset Tetap
- `current_liability`: Kewajiban Lancar
- `long_term_liability`: Kewajiban Jangka Panjang
- `equity`: Ekuitas
- `operating_revenue`: Pendapatan Usaha
- `other_revenue`: Pendapatan Lain-lain
- `operating_expense`: Beban Operasional
- `other_expense`: Beban Lain-lain

### Industry-Specific Account Examples

**Distributor:**
- 1-1410: Persediaan Barang Dagangan
- 5-1100: Harga Pokok Penjualan
- 5-2100: Biaya Pengiriman
- 5-2110: Komisi Penjualan
- 4-1100: Diskon Penjualan (contra revenue)
- 5-1200: Retur Pembelian (contra expense)

**Retail:**
- 1-1120: Kas Toko
- 1-1130: Kas Register
- 5-2200: Biaya Sewa Toko
- 5-2210: Biaya Promosi
- 5-2220: Biaya Packaging
- 5-2900: Selisih Kas

**Service:**
- 4-1100: Pendapatan Jasa Konsultasi
- 4-1200: Pendapatan Jasa Maintenance
- 1-1500: Piutang Belum Ditagih (Unbilled)
- 2-1300: Pendapatan Diterima Dimuka (Deferred)
- 5-1100: Biaya Proyek Langsung
- 1-1600: Pekerjaan Dalam Proses (WIP)

---

## Tasks & Subtasks

### Task 1: Create Command Structure
- [ ] Buat file `app/Console/Commands/GenerateCoACommand.php`
- [ ] Implement signature dengan parameter industry dan flag --force
- [ ] Implement handle() method dengan validation
- [ ] Implement confirmation prompt untuk delete existing
- [ ] Implement seeder dispatcher berdasarkan industry
- [ ] Implement summary display dengan table output
- [ ] Register command di `app/Console/Kernel.php` (jika perlu)

### Task 2: Create Seeder Base Structure
- [ ] Buat folder `database/seeders/coa/`
- [ ] Buat `GeneralCoASeeder.php` dengan struktur dasar
- [ ] Implement getAccounts() method
- [ ] Implement validateAccount() method
- [ ] Implement DB::transaction() wrapper
- [ ] Test dengan `php artisan db:seed --class=Database\\Seeders\\CoA\\GeneralCoASeeder`

### Task 3: Implement General Template (30+ accounts)
- [ ] Define 5 kategori utama (Asset, Liability, Equity, Revenue, Expense)
- [ ] Define Aset Lancar accounts (Kas, Bank, Piutang, Persediaan)
- [ ] Define Aset Tetap accounts (Tanah, Bangunan, Kendaraan, Akumulasi Penyusutan)
- [ ] Define Kewajiban accounts (Hutang Usaha, Hutang Bank, Hutang Pajak)
- [ ] Define Ekuitas accounts (Modal, Laba Ditahan, Laba Tahun Berjalan)
- [ ] Define Pendapatan accounts (Pendapatan Usaha, Pendapatan Lain-lain)
- [ ] Define Beban accounts (HPP, Beban Operasional, Beban Lain-lain)
- [ ] Verify total minimal 30 detail accounts
- [ ] Test seeder execution

### Task 4: Implement Distributor Template (45+ accounts)
- [ ] Copy GeneralCoASeeder.php → DistributorCoASeeder.php
- [ ] Tambahkan akun Persediaan Barang Dagangan (dengan sub-kategori)
- [ ] Tambahkan akun Biaya Pengiriman/Freight
- [ ] Tambahkan akun Komisi Penjualan
- [ ] Tambahkan akun Retur Penjualan & Pembelian
- [ ] Tambahkan akun Diskon Penjualan & Pembelian
- [ ] Tambahkan akun Biaya Gudang
- [ ] Verify total minimal 45 detail accounts
- [ ] Test seeder execution

### Task 5: Implement Retail Template (40+ accounts)
- [ ] Copy GeneralCoASeeder.php → RetailCoASeeder.php
- [ ] Tambahkan akun Kas Toko/Cash Register
- [ ] Tambahkan akun Persediaan Toko
- [ ] Tambahkan akun Biaya Sewa Toko
- [ ] Tambahkan akun Biaya Promosi & Marketing
- [ ] Tambahkan akun Biaya Packaging
- [ ] Tambahkan akun Selisih Kas (Cash Over/Short)
- [ ] Verify total minimal 40 detail accounts
- [ ] Test seeder execution

### Task 6: Implement Service Template (35+ accounts)
- [ ] Copy GeneralCoASeeder.php → ServiceCoASeeder.php
- [ ] Tambahkan akun Pendapatan Jasa (dengan sub-kategori)
- [ ] Tambahkan akun Professional Fees
- [ ] Tambahkan akun Project Costs
- [ ] Tambahkan akun Unbilled Revenue
- [ ] Tambahkan akun Deferred Revenue
- [ ] Tambahkan akun Work in Progress
- [ ] Verify total minimal 35 detail accounts
- [ ] Test seeder execution

### Task 7: Create Documentation
- [ ] Buat `database/seeders/coa/README.md`
- [ ] Document cara menggunakan command
- [ ] Document daftar template available
- [ ] Document struktur akun per template (table format)
- [ ] Document cara menambah template baru
- [ ] Tambahkan inline comments di seeder untuk kategori akun

### Task 8: Testing & Verification
- [ ] Test command dengan parameter invalid
- [ ] Test command dengan database kosong
- [ ] Test command dengan existing accounts (dengan konfirmasi)
- [ ] Test command dengan flag --force
- [ ] Test setiap template seeder independent
- [ ] Verify account count per template
- [ ] Verify hierarki parent-child correct
- [ ] Verify no duplicate codes
- [ ] Test rollback jika ada error (transaction)

---

## Testing Requirements

### Unit Tests
```php
// tests/Unit/Commands/GenerateCoACommandTest.php
test('command validates industry parameter')
test('command confirms before deleting existing accounts')
test('command skips confirmation with force flag')
test('command displays summary after generation')

// tests/Unit/Seeders/CoA/GeneralCoASeederTest.php
test('general template creates minimum 30 accounts')
test('general template has correct hierarchy')
test('general template has no duplicate codes')
test('general template validates required fields')
```

### Feature Tests
```php
// tests/Feature/CoA/GenerateCoATest.php
test('can generate general template')
test('can generate distributor template')
test('can generate retail template')
test('can generate service template')
test('command deletes existing accounts before generation')
test('command rolls back on error')
```

### Manual Testing Checklist
- [ ] Run `php artisan coa:generate general` → verify 30+ accounts
- [ ] Run `php artisan coa:generate distributor` → verify 45+ accounts
- [ ] Run `php artisan coa:generate retail` → verify 40+ accounts
- [ ] Run `php artisan coa:generate service` → verify 35+ accounts
- [ ] Run command twice → verify confirmation prompt
- [ ] Run with --force → verify no prompt
- [ ] Check account hierarchy di UI (Account Index page)
- [ ] Verify no broken parent_id references

---

## Definition of Done

- [ ] Command `php artisan coa:generate {industry}` berfungsi untuk 4 templates
- [ ] Setiap template memiliki jumlah akun sesuai AC (30/45/40/35)
- [ ] Hierarki parent-child correct untuk semua templates
- [ ] Command memiliki konfirmasi sebelum delete existing
- [ ] Command memiliki flag --force untuk automation
- [ ] Command menampilkan summary setelah generation
- [ ] Documentation lengkap di README.md
- [ ] Inline comments di seeder menjelaskan kategori
- [ ] Unit tests pass untuk command dan seeders
- [ ] Feature tests pass untuk semua templates
- [ ] Manual testing checklist complete
- [ ] Code review approved
- [ ] Sprint status updated ke 'done'

---

## Notes

**Priority:** Medium - Nice to have tapi tidak blocking untuk core functionality. User bisa manual create accounts jika perlu.

**Estimated Effort:** 4-6 hours
- Command implementation: 1 hour
- General template: 1 hour
- 3 industry templates: 2 hours
- Documentation: 30 minutes
- Testing: 1.5 hours

**Dependencies:**
- Story 4.1 (Chart of Accounts Structure) harus complete
- Account model dan migration sudah ada
- Database seeder pattern sudah established

**Future Enhancements:**
- Template untuk industri lain (Manufacturing, Construction, Healthcare)
- Import template dari file (CSV/Excel)
- Export template ke file untuk sharing
- UI untuk preview template sebelum generate
- Merge template (add accounts tanpa delete existing)

---

**Created by:** BMad Create Story Workflow  
**Last Updated:** 2026-05-14
