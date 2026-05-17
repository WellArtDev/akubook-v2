# Chart of Accounts Templates

Template Chart of Accounts (CoA) untuk berbagai industri di Indonesia.

## Cara Menggunakan

### Generate Template via Command

```bash
php artisan coa:generate {industry}
```

**Parameter `industry`:**
- `general` - Template umum untuk semua jenis bisnis
- `distributor` - Template untuk bisnis distributor
- `retail` - Template untuk bisnis retail/toko
- `service` - Template untuk bisnis jasa

**Contoh:**
```bash
# Generate template general
php artisan coa:generate general

# Generate template distributor
php artisan coa:generate distributor

# Generate dengan force (skip konfirmasi)
php artisan coa:generate retail --force
```

### Konfirmasi Delete

Jika sudah ada accounts di database, command akan meminta konfirmasi sebelum menghapus:

```
Found 45 existing accounts. Delete and regenerate? (yes/no) [no]:
```

Gunakan flag `--force` untuk skip konfirmasi (berguna untuk automation/testing).

## Template Available

### 1. General (Umum)

**Total Accounts:** 44 (30+ detail accounts)

**Struktur:**
- **Aset (1-0000)**
  - Aset Lancar: Kas, Bank, Piutang, Persediaan, Uang Muka, Biaya Dibayar Dimuka
  - Aset Tetap: Tanah, Bangunan, Kendaraan, Peralatan Kantor (dengan akumulasi penyusutan)
- **Kewajiban (2-0000)**
  - Kewajiban Lancar: Hutang Usaha, Hutang Pajak, Hutang Gaji
  - Kewajiban Jangka Panjang: Hutang Bank
- **Ekuitas (3-0000)**
  - Modal, Laba Ditahan, Laba Tahun Berjalan
- **Pendapatan (4-0000)**
  - Pendapatan Usaha, Pendapatan Lain-lain
- **Beban (5-0000)**
  - Harga Pokok Penjualan
  - Beban Operasional: Gaji, Sewa, Listrik & Air, Telepon & Internet, Penyusutan, Administrasi, Pemeliharaan
  - Beban Lain-lain

**Cocok untuk:** Bisnis umum yang tidak masuk kategori spesifik

---

### 2. Distributor

**Total Accounts:** 57 (45+ detail accounts)

**Tambahan dari General:**
- **Persediaan Barang Dagangan** (dengan sub-kategori)
  - Persediaan Barang Jadi
  - Persediaan Dalam Perjalanan
- **Peralatan Gudang** (dengan akumulasi penyusutan)
- **Retur Penjualan** (contra revenue)
- **Diskon Penjualan** (contra revenue)
- **Retur Pembelian** (contra expense)
- **Diskon Pembelian** (contra expense)
- **Biaya Pengiriman**
- **Komisi Penjualan**
- **Biaya Gudang**
- **Beban Transportasi**

**Cocok untuk:** Distributor sound system, distributor elektronik, distributor FMCG, dll.

---

### 3. Retail

**Total Accounts:** 52 (40+ detail accounts)

**Tambahan dari General:**
- **Kas Toko**
- **Kas Register**
- **Persediaan Toko**
- **Peralatan Toko** (dengan akumulasi penyusutan)
- **Beban Sewa Toko**
- **Beban Promosi & Marketing**
- **Beban Packaging**
- **Selisih Kas** (Cash Over/Short)

**Cocok untuk:** Toko roti, minimarket, toko pakaian, toko elektronik, dll.

---

### 4. Service

**Total Accounts:** 49 (35+ detail accounts)

**Tambahan dari General:**
- **Piutang Belum Ditagih** (Unbilled Revenue)
- **Pekerjaan Dalam Proses** (Work in Progress)
- **Pendapatan Diterima Dimuka** (Deferred Revenue)
- **Pendapatan Jasa Konsultasi**
- **Pendapatan Jasa Maintenance**
- **Professional Fees**
- **Biaya Proyek Langsung**
- **Beban Pelatihan & Pengembangan**

**Cocok untuk:** Konsultan, software house, jasa maintenance, jasa profesional, dll.

---

## Struktur Kode Akun

Format: `X-XXXX`

**Digit Pertama (Kategori Utama):**
- `1` - Aset
- `2` - Kewajiban
- `3` - Ekuitas
- `4` - Pendapatan
- `5` - Beban

**Digit Kedua (Sub-kategori):**
- `X-1XXX` - Kategori Lancar/Utama
- `X-2XXX` - Kategori Jangka Panjang/Sekunder
- `X-3XXX` - Kategori Lain-lain

**Contoh:**
- `1-1100` - Kas (Aset Lancar)
- `1-2200` - Bangunan (Aset Tetap)
- `2-1100` - Hutang Usaha (Kewajiban Lancar)
- `4-1000` - Pendapatan Usaha
- `5-2100` - Beban Gaji (Beban Operasional)

---

## Hierarki Akun

Setiap template menggunakan hierarki 3 level:

1. **Level 1 (Header):** Kategori utama (ASET, KEWAJIBAN, EKUITAS, PENDAPATAN, BEBAN)
2. **Level 2 (Sub-header):** Sub-kategori (Aset Lancar, Aset Tetap, dll)
3. **Level 3 (Detail):** Akun detail yang dapat diposting (Kas, Bank, Piutang, dll)

**Aturan Posting:**
- Hanya akun **Level 3** (detail) yang dapat digunakan untuk posting transaksi
- Akun Level 1 & 2 adalah header untuk grouping dan reporting

---

## Cara Menambah Template Baru

1. **Buat Seeder Class Baru**

   Buat file di `database/seeders/CoA/` dengan nama `{Industry}CoASeeder.php`

   ```php
   <?php
   
   namespace Database\Seeders\CoA;
   
   use App\Models\Account;
   use Illuminate\Database\Seeder;
   use Illuminate\Support\Facades\DB;
   
   class ManufacturingCoASeeder extends Seeder
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
               // Define accounts here
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

2. **Update Command**

   Edit `app/Console/Commands/GenerateCoACommand.php`:

   ```php
   // Tambahkan di validIndustries
   $validIndustries = ['general', 'distributor', 'retail', 'service', 'manufacturing'];
   
   // Tambahkan di match statement
   $seederClass = match($industry) {
       'general' => GeneralCoASeeder::class,
       'distributor' => DistributorCoASeeder::class,
       'retail' => RetailCoASeeder::class,
       'service' => ServiceCoASeeder::class,
       'manufacturing' => ManufacturingCoASeeder::class,
   };
   ```

3. **Update README**

   Tambahkan dokumentasi template baru di section "Template Available"

---

## Testing

### Manual Testing

```bash
# Test General Template
php artisan coa:generate general
php artisan tinker
>>> Account::count()
=> 44

# Test Distributor Template
php artisan coa:generate distributor --force
>>> Account::count()
=> 57

# Test Retail Template
php artisan coa:generate retail --force
>>> Account::count()
=> 52

# Test Service Template
php artisan coa:generate service --force
>>> Account::count()
=> 49
```

### Verify Hierarchy

```bash
php artisan tinker
>>> Account::headers()->count()  # Should show header accounts
>>> Account::details()->count()  # Should show detail accounts
>>> Account::where('parent_id', null)->get()  # Should show level 1 headers
```

---

## Troubleshooting

### Error: "Missing required field"

Pastikan setiap account memiliki field required:
- `code`
- `name`
- `type`
- `level`
- `is_header`
- `is_active`

### Error: "Integrity constraint violation"

Pastikan `parent_id` mengacu ke ID yang valid. Insert accounts secara berurutan (parent dulu, child kemudian).

### Accounts tidak muncul di UI

Pastikan `is_active = true` dan `is_header = false` untuk detail accounts.

---

## Maintenance

### Update Existing Template

1. Edit seeder file di `database/seeders/CoA/`
2. Run command dengan `--force` untuk regenerate
3. Test di development environment dulu sebelum production

### Backup Before Regenerate

```bash
# Backup accounts table
php artisan db:backup accounts

# Or export to CSV
php artisan tinker
>>> Account::all()->toCsv('accounts_backup.csv')
```

---

## Support

Untuk pertanyaan atau issue, hubungi tim development atau buat issue di repository project.
