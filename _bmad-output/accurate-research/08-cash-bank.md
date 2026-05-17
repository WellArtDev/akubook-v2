# Cash & Bank (Kas & Bank) Module

**Priority**: HIGH (Core MVP Module)  
**Source**: Accurate Online Help Documentation  
**Last Updated**: May 2026

---

## Overview

Cash & Bank (Kas & Bank) module core untuk cash flow management di Accurate Online. Handle semua cash transactions, bank accounts, payments, receipts, transfers, dan bank reconciliation. Support multi-currency accounts, bank feeds integration, auto-reconciliation, dan Indonesian banking practices (giro, cek, transfer methods).

**Key Capabilities**:
- Multi-currency cash/bank accounts
- Payment & receipt recording (tunai, transfer, cek/giro, virtual account)
- Bank transfers with fee tracking
- Bank reconciliation (manual & bulk)
- Bank feed integration (Smartlink e-Banking)
- Check/giro management & void handling
- Foreign currency transaction recording
- Bank statement import & matching

---

## Core Features (7 Features)

### 1. Cash & Bank Account Management
**Function**: Manage multiple cash/bank accounts with multi-currency support

**Features**:
- Create cash/bank accounts (Kas, Bank, Petty Cash)
- Multi-currency account support (IDR, USD, EUR, etc.)
- Default currency per account
- Account balance tracking
- Bank account linking (for reconciliation)

**Technical Notes**:
- Account type: Kas/Bank (Chart of Accounts)
- Each account can have ONE default currency
- Currency exchange rate auto-calculated per transaction
- Account history viewable via "Histori Bank" feature

**Indonesian Banking Context**:
- Support for local banks (BCA, Mandiri, BNI, BRI, etc.)
- Corporate ID support for corporate banking
- Account number format validation

---

### 2. Cash Receipts (Penerimaan)
**Function**: Record cash/bank receipts from any source

**Features**:
- General cash receipt recording
- Multi-currency receipt support
- Receipt from sales (via Penerimaan Penjualan)
- Receipt from other sources (via Penerimaan)
- Check/giro receipt recording
- Receipt voucher printing

**Transaction Flow**:
1. Access: Kas & Bank → Penerimaan
2. Select cash/bank account
3. Enter transaction date & exchange rate (if foreign currency)
4. Select account perkiraan (GL account)
5. Enter amount
6. Add info: Check number, payer name, notes
7. Save → Auto-journal created

**Multi-Currency Receipt**:
- Must select cash/bank account with foreign currency default
- Exchange rate auto-populated (can be overridden)
- Amount entered in foreign currency
- Journal shows both foreign & IDR amounts

**Check/Giro Handling**:
- Record check/giro number
- Record check/giro date
- Void feature for bounced checks (see Feature 7)

---

### 3. Cash Payments (Pembayaran)
**Function**: Record cash/bank payments for any expense

**Features**:
- General cash payment recording
- Multi-currency payment support
- Payment to suppliers (via Pembayaran Pembelian)
- Payment for expenses (via Pembayaran)
- Check/giro payment recording
- Payment voucher printing

**Transaction Flow**:
1. Access: Kas & Bank → Pembayaran
2. Select cash/bank account
3. Enter transaction date
4. Select account perkiraan (expense/liability account)
5. Enter amount
6. Add info: Check number, payee name, notes
7. Save → Auto-journal created

**Payment Methods Supported**:
- **Tunai (Cash)**: Direct cash payment
- **Transfer Bank**: Bank transfer (domestic/international)
- **Cek/Giro**: Check/giro payment
- **Virtual Account**: VA payment (for supplier invoices)

**Perintah Pembayaran (Payment Order)**:
- Batch payment planning feature
- Select multiple unpaid purchase invoices
- Set payment deadline (Tgl Batas Transfer)
- Choose payment method: Transfer Bank, Virtual Account, Cek/Giro
- Enter bank account number per supplier
- System creates Pembayaran Pembelian transactions with status "Belum Ditransfer"
- After actual transfer, record via Transfer Pemasok → status changes to "Sudah Diproses"
- System creates Transfer Bank transaction to reverse temporary account

**Use Case**: Manage scheduled payments to multiple suppliers efficiently

---

### 4. Bank Transfers (Transfer Bank)
**Function**: Record inter-bank/inter-cash account transfers

**Features**:
- Transfer between cash/bank accounts
- Transfer fee recording
- Multi-currency transfer support
- Transfer notes/memo
- Auto-journal for both accounts

**Transaction Flow**:
1. Access: Kas & Bank → Transfer Bank
2. Enter transaction date
3. Tab "Transfer Uang":
   - Select "Dari Kas/Bank" (source account)
   - Select "Ke Kas/Bank" (destination account)
   - Enter transfer amount
4. Tab "Biaya Transfer" (optional):
   - Add transfer fees
   - Select expense account for fees
5. Tab "Informasi Lainnya":
   - Add notes/memo
6. Save → Auto-journal created

**Journal Impact**:
- Debit: Destination account
- Credit: Source account
- Debit: Transfer fee expense (if any)
- Credit: Source account (for fee)

**Use Cases**:
- Transfer between bank accounts
- Transfer from bank to petty cash
- Transfer for cash deposit
- Transfer for cash withdrawal

---

### 5. Bank Reconciliation (Rekonsiliasi Bank)
**Function**: Match bank statement transactions with recorded transactions

**Features**:
- Manual reconciliation (one-by-one)
- Bulk reconciliation (mass matching)
- Bank statement import (CSV/Excel)
- Smartlink e-Banking integration
- Unmatched transaction handling
- Reconciliation report

**Reconciliation Process**:

#### A. Setup Smartlink e-Banking (One-time)
1. Access: Kas & Bank → Smartlink e-Banking
2. Click "Tambah" (Add)
3. Enter:
   - Jenis Internet Banking (bank name)
   - No. Rekening Bank (account number)
   - Relasi Akun Bank (link to Accurate cash/bank account)
   - Corporate ID (if corporate banking)
4. Save

#### B. Import Bank Statement
1. Access: Kas & Bank → Rekening Koran
2. Select cash/bank account
3. Select date range
4. Click "Impor Data" → "Impor File Mutasi"
5. Upload CSV/Excel file (from internet banking)
6. System displays imported transactions

**Supported Formats**:
- Accurate Online Format (custom Excel template)
- Bank-specific formats (BCA, Mandiri, BNI, etc.)

#### C. Perform Reconciliation
**Manual Reconciliation**:
1. Access: Kas & Bank → Rekonsiliasi Bank
2. Select cash/bank account
3. Select date range
4. Click "Perbarui" (Refresh)
5. Match bank statement lines with recorded transactions
6. Click "Proses" for each matched pair

**Bulk Reconciliation**:
1. After importing bank statement (step B)
2. System auto-matches transactions based on:
   - Date
   - Amount
   - Description (if available)
3. Review matched transactions
4. Click "Proses semua data cocok" (Process all matched)
5. All matched transactions reconciled instantly

**Unmatched Transactions**:
- Bank statement line without matching transaction → Create new transaction
- Recorded transaction without bank statement line → Investigate (missing from bank or wrong date)

**Reconciliation Report**:
- Shows reconciled vs unreconciled transactions
- Shows bank balance vs book balance
- Identifies discrepancies

---

### 6. Bank Statement Management (Rekening Koran)
**Function**: Import, view, and manage bank statement data

**Features**:
- Import bank statement (CSV/Excel)
- View bank statement transactions
- Delete bank statement entries (single/bulk)
- Bank statement history
- Export bank statement data

**Import Process**:
1. Access: Kas & Bank → Rekening Koran
2. Select cash/bank account
3. Click "Impor Data" → "Impor File Mutasi"
4. Choose import method:
   - **Direct from Internet Banking**: If Smartlink e-Banking configured
   - **Upload File**: Upload CSV/Excel file
5. System validates & imports transactions
6. View imported transactions in Rekening Koran

**Smartlink e-Banking Format**:
- Download template: Kas/Bank → Rekening Koran → Impor Data → "Disini" (download link)
- Fill template with bank statement data:
  - Tanggal (Date)
  - Keterangan (Description)
  - Debit (Debit amount)
  - Kredit (Credit amount)
  - Saldo (Balance)
- Upload filled template

**Delete Bank Statement Entries**:
- **Single Delete**: Click trash icon on transaction row
- **Bulk Delete**:
  1. Access: Kas & Bank → Rekonsiliasi Bank
  2. Select cash/bank account & date range
  3. Check transactions to delete
  4. Click "Hapus rekening koran yang terpilih"
  5. Confirm deletion

**Use Cases**:
- Import monthly bank statements
- Review bank transactions before reconciliation
- Clean up duplicate/incorrect imports
- Maintain bank statement history

---

### 7. Check/Giro Management
**Function**: Handle check/giro payments & receipts, including bounced checks

**Features**:
- Check/giro receipt recording
- Check/giro payment recording
- Void bounced checks (cek kosong)
- Check/giro tracking
- Check/giro printing

**Check/Giro Receipt (Penerimaan Penjualan)**:
1. Access: Penjualan → Penerimaan Penjualan
2. Select customer & invoice
3. Enter payment details:
   - Metode Bayar: Cek/Giro
   - No. Cek/Giro
   - Tanggal Cek/Giro
4. Save → Invoice marked as paid

**Void Bounced Check (Cek Kosong)**:
When check/giro bounces or is rejected by bank:

1. Access: Penjualan → Penerimaan Penjualan
2. Find & open the check/giro receipt transaction
3. Go to tab "Info Lainnya"
4. Check "Void" option
5. Save

**System Actions on Void**:
- Creates reversal journal (auto)
- Invoice status changes back to "Belum Lunas" (Unpaid)
- Bank history shows negative transaction with note "cek kosong"
- Audit trail recorded in Laporan Rincian Buku Besar Pembantu Piutang

**Record Replacement Check**:
1. Create new Penerimaan Penjualan transaction
2. Select same invoice (now unpaid again)
3. Enter new check/giro details
4. Save → Invoice marked as paid again

**Check/Giro Payment (Pembayaran Pembelian)**:
- Similar process for supplier payments
- Record check/giro number & date
- Track check/giro status

**Indonesian Banking Context**:
- **Cek**: Check (immediate payment instrument)
- **Giro**: Post-dated check (payment on future date)
- Both widely used in Indonesian B2B transactions
- Bounced checks common issue → void feature critical

---

## References

- [Accurate Online - Kas & Bank](https://help.accurate.id/product/kas-bank/)
- [Rekonsiliasi Bank Massal](https://help.accurate.id/product/accurate-online/fitur-aol/kas-bank/rekonsiliasi-bank/rekonsiliasi-bank-secara-massal/)
- [Smartlink e-Banking](https://help.accurate.id/product/accurate-online/fitur-aol/kas-bank/smartlink-ebanking/smartlink-e-Banking-format-aol)
- [Void Penerimaan Cek/Giro](https://help.accurate.id/product/accurate-online/fitur-aol/penjualan/penerimaan-penjualan/void-penerimaan-cek/)
- [Penerimaan Mata Uang Asing](https://help.accurate.id/product/accurate-online/fitur-aol/kas-bank/penerimaan/membuat-penerimaan-mata-uang-asing/)
- [Perintah Pembayaran](https://help.accurate.id/product/accurate-online/fitur-aol/pembelian/perintah-pembayaran/membuat-perintah-pembayaran/)
- [Transfer Bank](https://help.accurate.id/product/accurate-online/fitur-aol/kas-bank/transfer-bank/membuat-transfer-bank/)

---

**Document Status**: ✅ Complete  
**Coverage**: 7/7 features documented  
**Next Steps**: Review with team, validate against actual Accurate Online UI, plan AkuBook implementation
