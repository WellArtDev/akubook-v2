<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\FiscalPeriod;
use App\Models\Item;
use App\Models\JournalEntry;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class HistoricalTransactionsImportService
{
    private const MODULES = ['sales_orders', 'purchase_orders', 'customer_payments'];

    public function preview(array $payload): array
    {
        $startedAt = microtime(true);
        $transactions = $this->parse($payload);
        $mapped = $this->map($transactions);
        $validated = $this->validate($mapped);

        return $this->summary($validated, false, $startedAt);
    }

    public function import(array $payload): array
    {
        $startedAt = microtime(true);
        $transactions = $this->parse($payload);
        $mapped = $this->map($transactions);
        $validated = $this->validate($mapped);
        $summary = $this->summary($validated, false, $startedAt);

        if ($summary['valid'] === 0) {
            $summary['executed'] = false;
            $summary['message'] = 'Tidak ada transaksi valid untuk diimport.';

            return $summary;
        }

        return DB::transaction(function () use ($validated, $startedAt) {
            $imported = 0;
            $journalCount = 0;
            $perModuleImported = array_fill_keys(self::MODULES, 0);

            foreach (self::MODULES as $module) {
                foreach ($validated[$module] as $row) {
                    if (! $row['valid']) {
                        continue;
                    }

                    $result = match ($module) {
                        'sales_orders' => $this->persistSalesOrder($row['data']),
                        'purchase_orders' => $this->persistPurchaseOrder($row['data']),
                        'customer_payments' => $this->persistCustomerPayment($row['data']),
                    };

                    $imported++;
                    $perModuleImported[$module]++;
                    $journalCount += $result['journal_created'] ? 1 : 0;
                }
            }

            $summary = $this->summary($validated, true, $startedAt);
            $summary['imported'] = $imported;
            $summary['imported_by_module'] = $perModuleImported;
            $summary['journal_count'] = $journalCount;

            return $summary;
        });
    }

    private function parse(array $payload): array
    {
        $source = $payload['historical_transactions']
            ?? $payload['transactions']
            ?? data_get($payload, 'data.transactions')
            ?? data_get($payload, 'rows.transactions')
            ?? $payload;

        if (! is_array($source)) {
            throw new InvalidArgumentException('Payload transaksi historis harus berupa array.');
        }

        $transactions = array_fill_keys(self::MODULES, []);

        foreach (self::MODULES as $module) {
            $rows = $source[$module] ?? [];

            if (! is_array($rows)) {
                throw new InvalidArgumentException("Payload {$module} harus berupa array.");
            }

            $transactions[$module] = array_values($rows);
        }

        if (collect($transactions)->flatten(1)->isEmpty()) {
            throw new InvalidArgumentException('Payload transaksi historis kosong.');
        }

        return $transactions;
    }

    private function map(array $transactions): array
    {
        return [
            'sales_orders' => $this->mapSalesOrders($transactions['sales_orders']),
            'purchase_orders' => $this->mapPurchaseOrders($transactions['purchase_orders']),
            'customer_payments' => $this->mapCustomerPayments($transactions['customer_payments']),
        ];
    }

    private function mapSalesOrders(array $rows): array
    {
        return array_map(fn (array $row) => [
            'document_number' => $this->stringValue($row, ['so_number', 'document_number', 'number', 'no_bukti']),
            'date' => $this->stringValue($row, ['so_date', 'date', 'transaction_date', 'tanggal']),
            'customer_code' => $this->nullableStringValue($row, ['customer_code', 'customer_no', 'kode_pelanggan']),
            'customer_id' => $this->nullableIntValue($row, ['customer_id']),
            'status' => $this->stringValue($row, ['status'], 'draft'),
            'created_by' => $this->nullableIntValue($row, ['created_by', 'user_id']),
            'sales_person_id' => $this->nullableIntValue($row, ['sales_person_id', 'sales_id', 'user_id']),
            'notes' => $this->nullableStringValue($row, ['notes', 'description', 'keterangan']),
            'lines' => $this->mapSalesOrderLines($row['lines'] ?? $row['items'] ?? []),
            'journal_lines' => $this->mapJournalLines($row['journal_lines'] ?? []),
        ], $rows);
    }

    private function mapSalesOrderLines(array $rows): array
    {
        return array_map(fn (array $row, int $index) => [
            'line_number' => $this->intValue($row, ['line_number', 'line'], $index + 1),
            'item_code' => $this->nullableStringValue($row, ['item_code', 'product_code', 'code']),
            'item_id' => $this->nullableIntValue($row, ['item_id', 'product_id']),
            'description' => $this->nullableStringValue($row, ['description', 'name', 'product_name']),
            'quantity' => $this->floatValue($row, ['quantity', 'qty'], 0),
            'unit' => $this->stringValue($row, ['unit', 'satuan'], 'pcs'),
            'unit_price' => $this->floatValue($row, ['unit_price', 'price', 'harga'], 0),
            'discount_percent' => $this->floatValue($row, ['discount_percent'], 0),
            'discount_amount' => $this->floatValue($row, ['discount_amount', 'discount'], 0),
            'tax_amount' => $this->floatValue($row, ['tax_amount', 'tax'], 0),
            'line_total' => $this->floatValue($row, ['line_total', 'total'], 0),
        ], $rows, array_keys($rows));
    }

    private function mapPurchaseOrders(array $rows): array
    {
        return array_map(fn (array $row) => [
            'document_number' => $this->stringValue($row, ['po_number', 'document_number', 'number', 'no_bukti']),
            'date' => $this->stringValue($row, ['po_date', 'date', 'transaction_date', 'tanggal']),
            'supplier_code' => $this->nullableStringValue($row, ['supplier_code', 'vendor_code', 'code']),
            'supplier_id' => $this->nullableIntValue($row, ['supplier_id']),
            'status' => $this->stringValue($row, ['status'], 'draft'),
            'created_by' => $this->nullableIntValue($row, ['created_by', 'user_id']),
            'notes' => $this->nullableStringValue($row, ['notes', 'description', 'keterangan']),
            'lines' => $this->mapPurchaseOrderLines($row['lines'] ?? $row['items'] ?? []),
            'journal_lines' => $this->mapJournalLines($row['journal_lines'] ?? []),
        ], $rows);
    }

    private function mapPurchaseOrderLines(array $rows): array
    {
        return array_map(fn (array $row, int $index) => [
            'line_number' => $this->intValue($row, ['line_number', 'line'], $index + 1),
            'product_code' => $this->stringValue($row, ['product_code', 'item_code', 'code']),
            'product_name' => $this->stringValue($row, ['product_name', 'item_name', 'name'], 'Imported item'),
            'description' => $this->nullableStringValue($row, ['description', 'keterangan']),
            'quantity' => $this->floatValue($row, ['quantity', 'qty'], 0),
            'unit' => $this->stringValue($row, ['unit', 'satuan'], 'pcs'),
            'unit_price' => $this->floatValue($row, ['unit_price', 'price', 'harga'], 0),
            'tax_amount' => $this->floatValue($row, ['tax_amount', 'tax'], 0),
            'line_total' => $this->floatValue($row, ['line_total', 'total'], 0),
        ], $rows, array_keys($rows));
    }

    private function mapCustomerPayments(array $rows): array
    {
        return array_map(fn (array $row) => [
            'document_number' => $this->stringValue($row, ['payment_number', 'document_number', 'number', 'no_bukti']),
            'date' => $this->stringValue($row, ['payment_date', 'date', 'transaction_date', 'tanggal']),
            'customer_code' => $this->nullableStringValue($row, ['customer_code', 'customer_no', 'kode_pelanggan']),
            'customer_id' => $this->nullableIntValue($row, ['customer_id']),
            'payment_method' => $this->stringValue($row, ['payment_method', 'method'], 'cash'),
            'reference_number' => $this->nullableStringValue($row, ['reference_number', 'reference']),
            'total_amount' => $this->floatValue($row, ['total_amount', 'amount', 'nilai'], 0),
            'status' => $this->stringValue($row, ['status'], 'draft'),
            'created_by' => $this->nullableIntValue($row, ['created_by', 'user_id']),
            'notes' => $this->nullableStringValue($row, ['notes', 'description', 'keterangan']),
            'journal_lines' => $this->mapJournalLines($row['journal_lines'] ?? []),
        ], $rows);
    }

    private function mapJournalLines(array $rows): array
    {
        return array_map(fn (array $row) => [
            'account_code' => $this->nullableStringValue($row, ['account_code', 'code', 'kode_akun']),
            'account_id' => $this->nullableIntValue($row, ['account_id']),
            'description' => $this->nullableStringValue($row, ['description', 'keterangan']),
            'debit' => $this->floatValue($row, ['debit'], 0),
            'credit' => $this->floatValue($row, ['credit'], 0),
        ], $rows);
    }

    private function validate(array $mapped): array
    {
        return [
            'sales_orders' => $this->validateSalesOrders($mapped['sales_orders']),
            'purchase_orders' => $this->validatePurchaseOrders($mapped['purchase_orders']),
            'customer_payments' => $this->validateCustomerPayments($mapped['customer_payments']),
        ];
    }

    private function validateSalesOrders(array $rows): array
    {
        return array_map(function (array $row) {
            $errors = $this->validateBaseDocument($row, ['draft', 'pending_approval', 'approved', 'in_progress', 'completed', 'cancelled']);
            $customer = $this->resolveCustomer($row);
            $createdBy = $this->resolveUser($row['created_by']);
            $salesPerson = $this->resolveUser($row['sales_person_id'] ?? $row['created_by']);

            if (! $customer) {
                $errors[] = 'Customer tidak ditemukan.';
            }

            if (! $createdBy || ! $salesPerson) {
                $errors[] = 'User created_by/sales_person_id tidak ditemukan.';
            }

            if ($row['lines'] === []) {
                $errors[] = 'Sales order minimal memiliki satu line.';
            }

            foreach ($row['lines'] as $index => $line) {
                $item = $this->resolveItem($line);

                if (! $item) {
                    $errors[] = "Line {$index}: item tidak ditemukan.";
                }

                if ($line['quantity'] <= 0) {
                    $errors[] = "Line {$index}: quantity harus lebih dari 0.";
                }
            }

            $journalErrors = $this->validateJournalRequirement($row, $row['status'] !== 'draft');
            $errors = array_merge($errors, $journalErrors);

            $row['customer_id'] = $customer?->id;
            $row['created_by'] = $createdBy?->id;
            $row['sales_person_id'] = $salesPerson?->id;
            $row['valid'] = $errors === [];
            $row['errors'] = $errors;

            return ['module' => 'sales_orders', 'document_number' => $row['document_number'], 'valid' => $row['valid'], 'errors' => $errors, 'data' => $row];
        }, $rows);
    }

    private function validatePurchaseOrders(array $rows): array
    {
        return array_map(function (array $row) {
            $errors = $this->validateBaseDocument($row, ['draft', 'pending_approval', 'approved', 'in_progress', 'completed', 'cancelled']);
            $supplier = $this->resolveSupplier($row);
            $createdBy = $this->resolveUser($row['created_by']);

            if (! $supplier) {
                $errors[] = 'Supplier tidak ditemukan.';
            }

            if (! $createdBy) {
                $errors[] = 'User created_by tidak ditemukan.';
            }

            if ($row['lines'] === []) {
                $errors[] = 'Purchase order minimal memiliki satu line.';
            }

            foreach ($row['lines'] as $index => $line) {
                if ($line['quantity'] <= 0) {
                    $errors[] = "Line {$index}: quantity harus lebih dari 0.";
                }
            }

            $journalErrors = $this->validateJournalRequirement($row, $row['status'] !== 'draft');
            $errors = array_merge($errors, $journalErrors);

            $row['supplier_id'] = $supplier?->id;
            $row['created_by'] = $createdBy?->id;
            $row['valid'] = $errors === [];
            $row['errors'] = $errors;

            return ['module' => 'purchase_orders', 'document_number' => $row['document_number'], 'valid' => $row['valid'], 'errors' => $errors, 'data' => $row];
        }, $rows);
    }

    private function validateCustomerPayments(array $rows): array
    {
        return array_map(function (array $row) {
            $errors = $this->validateBaseDocument($row, ['draft', 'posted', 'reconciled', 'voided']);
            $customer = $this->resolveCustomer($row);
            $createdBy = $this->resolveUser($row['created_by']);

            if (! in_array($row['payment_method'], ['cash', 'bank_transfer', 'check', 'credit_card', 'giro'], true)) {
                $errors[] = 'Payment method tidak valid.';
            }

            if ($row['total_amount'] <= 0) {
                $errors[] = 'Total payment harus lebih dari 0.';
            }

            if (! $customer) {
                $errors[] = 'Customer tidak ditemukan.';
            }

            if (! $createdBy) {
                $errors[] = 'User created_by tidak ditemukan.';
            }

            $journalErrors = $this->validateJournalRequirement($row, $row['status'] === 'posted' || $row['status'] === 'reconciled');
            $errors = array_merge($errors, $journalErrors);

            $row['customer_id'] = $customer?->id;
            $row['created_by'] = $createdBy?->id;
            $row['valid'] = $errors === [];
            $row['errors'] = $errors;

            return ['module' => 'customer_payments', 'document_number' => $row['document_number'], 'valid' => $row['valid'], 'errors' => $errors, 'data' => $row];
        }, $rows);
    }

    private function validateBaseDocument(array $row, array $allowedStatuses): array
    {
        $errors = [];

        if ($row['document_number'] === '') {
            $errors[] = 'Nomor dokumen wajib diisi.';
        }

        if (! $this->isValidDate($row['date'])) {
            $errors[] = 'Tanggal dokumen tidak valid.';
        }

        if (! in_array($row['status'], $allowedStatuses, true)) {
            $errors[] = 'Status dokumen tidak valid.';
        }

        return $errors;
    }

    private function validateJournalRequirement(array $row, bool $required): array
    {
        if (! $required) {
            return [];
        }

        if ($row['journal_lines'] === []) {
            return ['Transaksi posted wajib memiliki journal_lines.'];
        }

        $debit = 0;
        $credit = 0;
        $errors = [];

        foreach ($row['journal_lines'] as $index => $line) {
            $account = $this->resolveAccount($line);

            if (! $account) {
                $errors[] = "Journal line {$index}: account tidak ditemukan.";
            } elseif ($account->is_header) {
                $errors[] = "Journal line {$index}: header account tidak boleh dipakai.";
            }

            if ($line['debit'] < 0 || $line['credit'] < 0) {
                $errors[] = "Journal line {$index}: debit/credit tidak boleh negatif.";
            }

            if ($line['debit'] > 0 && $line['credit'] > 0) {
                $errors[] = "Journal line {$index}: debit dan credit tidak boleh sama-sama terisi.";
            }

            $debit += $line['debit'];
            $credit += $line['credit'];
        }

        if (round($debit, 2) !== round($credit, 2)) {
            $errors[] = 'Journal tidak balance.';
        }

        return $errors;
    }

    private function persistSalesOrder(array $row): array
    {
        $lines = $row['lines'];
        $journalLines = $row['journal_lines'];
        $subtotal = $this->sumLines($lines);
        $tax = array_sum(array_column($lines, 'tax_amount'));
        $discount = array_sum(array_column($lines, 'discount_amount'));

        $salesOrder = SalesOrder::updateOrCreate(
            ['so_number' => $row['document_number']],
            [
                'so_date' => $row['date'],
                'customer_id' => $row['customer_id'],
                'sales_person_id' => $row['sales_person_id'],
                'status' => $row['status'],
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'grand_total' => $subtotal + $tax,
                'notes' => $row['notes'],
                'created_by' => $row['created_by'],
            ]
        );

        $salesOrder->lines()->delete();

        foreach ($lines as $line) {
            $item = $this->resolveItem($line);
            $salesOrder->lines()->create([
                'line_number' => $line['line_number'],
                'item_id' => $item->id,
                'description' => $line['description'] ?: $item->name,
                'quantity' => $line['quantity'],
                'unit' => $line['unit'],
                'unit_price' => $line['unit_price'],
                'discount_percent' => $line['discount_percent'],
                'discount_amount' => $line['discount_amount'],
                'tax_amount' => $line['tax_amount'],
                'line_total' => $line['line_total'] ?: $line['quantity'] * $line['unit_price'] - $line['discount_amount'] + $line['tax_amount'],
            ]);
        }

        $journalCreated = $journalLines !== [];
        if ($journalCreated) {
            $this->createJournal($row, $journalLines, 'auto_sales', 'sales_order', $salesOrder->id);
        }

        return ['journal_created' => $journalCreated];
    }

    private function persistPurchaseOrder(array $row): array
    {
        $lines = $row['lines'];
        $journalLines = $row['journal_lines'];
        $subtotal = $this->sumLines($lines);
        $tax = array_sum(array_column($lines, 'tax_amount'));

        $purchaseOrder = PurchaseOrder::updateOrCreate(
            ['po_number' => $row['document_number']],
            [
                'po_date' => $row['date'],
                'supplier_id' => $row['supplier_id'],
                'status' => $row['status'],
                'subtotal' => $subtotal,
                'tax_amount' => $tax,
                'grand_total' => $subtotal + $tax,
                'notes' => $row['notes'],
                'created_by' => $row['created_by'],
            ]
        );

        $purchaseOrder->lines()->delete();

        foreach ($lines as $line) {
            $purchaseOrder->lines()->create([
                'line_number' => $line['line_number'],
                'product_code' => $line['product_code'],
                'product_name' => $line['product_name'],
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit' => $line['unit'],
                'unit_price' => $line['unit_price'],
                'tax_amount' => $line['tax_amount'],
                'line_total' => $line['line_total'] ?: $line['quantity'] * $line['unit_price'] + $line['tax_amount'],
            ]);
        }

        $journalCreated = $journalLines !== [];
        if ($journalCreated) {
            $this->createJournal($row, $journalLines, 'auto_purchase', 'purchase_order', $purchaseOrder->id);
        }

        return ['journal_created' => $journalCreated];
    }

    private function persistCustomerPayment(array $row): array
    {
        $journalLines = $row['journal_lines'];

        $payment = CustomerPayment::updateOrCreate(
            ['payment_number' => $row['document_number']],
            [
                'payment_date' => $row['date'],
                'customer_id' => $row['customer_id'],
                'payment_method' => $row['payment_method'],
                'reference_number' => $row['reference_number'],
                'total_amount' => $row['total_amount'],
                'allocated_amount' => 0,
                'unapplied_amount' => $row['total_amount'],
                'status' => $row['status'],
                'notes' => $row['notes'],
                'created_by' => $row['created_by'],
            ]
        );

        $journalCreated = $journalLines !== [];
        if ($journalCreated) {
            $journal = $this->createJournal($row, $journalLines, 'auto_receipt', 'customer_payment', $payment->id);
            $payment->update(['journal_entry_id' => $journal->id]);
        }

        return ['journal_created' => $journalCreated];
    }

    private function createJournal(array $row, array $journalLines, string $type, string $referenceType, int $referenceId): JournalEntry
    {
        $fiscalPeriod = FiscalPeriod::query()
            ->where('start_date', '<=', $row['date'])
            ->where('end_date', '>=', $row['date'])
            ->first();

        if (! $fiscalPeriod) {
            $fiscalPeriod = FiscalPeriod::query()->first();
        }

        $totalDebit = array_sum(array_column($journalLines, 'debit'));
        $totalCredit = array_sum(array_column($journalLines, 'credit'));

        $journal = JournalEntry::create([
            'journal_number' => $this->generateJournalNumber($referenceType),
            'journal_date' => $row['date'],
            'fiscal_period_id' => $fiscalPeriod?->id,
            'type' => $type,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => 'Historical import '.$row['document_number'],
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => $row['created_by'],
        ]);

        foreach ($journalLines as $line) {
            $account = $this->resolveAccount($line);
            $journal->lines()->create([
                'account_id' => $account->id,
                'description' => $line['description'] ?: 'Historical import '.$row['document_number'],
                'debit' => $line['debit'],
                'credit' => $line['credit'],
            ]);
        }

        return $journal;
    }

    private function summary(array $validated, bool $executed, float $startedAt): array
    {
        $perModule = [];
        $errors = [];
        $total = 0;
        $valid = 0;
        $skipped = 0;

        foreach (self::MODULES as $module) {
            $rows = $validated[$module];
            $moduleTotal = count($rows);
            $moduleValid = count(array_filter($rows, fn (array $row) => $row['valid']));
            $moduleSkipped = $moduleTotal - $moduleValid;

            $perModule[$module] = ['total' => $moduleTotal, 'valid' => $moduleValid, 'skipped' => $moduleSkipped];
            $total += $moduleTotal;
            $valid += $moduleValid;
            $skipped += $moduleSkipped;

            foreach ($rows as $row) {
                if (! $row['valid']) {
                    $errors[] = ['module' => $module, 'document_number' => $row['document_number'], 'errors' => $row['errors']];
                }
            }
        }

        return [
            'executed' => $executed,
            'total' => $total,
            'valid' => $valid,
            'imported' => 0,
            'skipped' => $skipped,
            'journal_count' => 0,
            'per_module' => $perModule,
            'errors' => $errors,
            'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
        ];
    }

    private function resolveCustomer(array $row): ?Customer
    {
        if ($row['customer_id']) {
            return Customer::find($row['customer_id']);
        }

        return Customer::where('code', $row['customer_code'])->first();
    }

    private function resolveSupplier(array $row): ?Supplier
    {
        if ($row['supplier_id']) {
            return Supplier::find($row['supplier_id']);
        }

        return Supplier::where('supplier_code', $row['supplier_code'])->first();
    }

    private function resolveItem(array $row): ?Item
    {
        if ($row['item_id']) {
            return Item::find($row['item_id']);
        }

        return Item::where('code', $row['item_code'] ?? $row['product_code'] ?? null)->first();
    }

    private function resolveAccount(array $row): ?Account
    {
        if ($row['account_id']) {
            return Account::find($row['account_id']);
        }

        return Account::where('code', $row['account_code'])->first();
    }

    private function resolveUser(?int $id): ?User
    {
        return $id ? User::find($id) : null;
    }

    private function sumLines(array $lines): float
    {
        return array_sum(array_map(fn (array $line) => $line['line_total'] ?: $line['quantity'] * $line['unit_price'], $lines));
    }

    private function generateJournalNumber(string $referenceType): string
    {
        $prefix = match ($referenceType) {
            'sales_order' => 'HSO',
            'purchase_order' => 'HPO',
            'customer_payment' => 'HCP',
            default => 'HIS',
        };

        $date = now()->format('Ymd');
        $base = $prefix.'-'.$date.'-';

        $lastNumber = JournalEntry::query()
            ->where('journal_number', 'like', $base.'%')
            ->lockForUpdate()
            ->orderByDesc('journal_number')
            ->value('journal_number');

        $nextSequence = $lastNumber ? ((int) substr((string) $lastNumber, -4)) + 1 : 1;

        return $base.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }

    private function isValidDate(string $date): bool
    {
        return $date !== '' && strtotime($date) !== false;
    }

    private function value(array $row, array $keys, mixed $default = null): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return $default;
    }

    private function stringValue(array $row, array $keys, string $default = ''): string
    {
        return trim((string) $this->value($row, $keys, $default));
    }

    private function nullableStringValue(array $row, array $keys): ?string
    {
        $value = $this->value($row, $keys);

        return $value === null ? null : trim((string) $value);
    }

    private function intValue(array $row, array $keys, int $default = 0): int
    {
        return (int) $this->value($row, $keys, $default);
    }

    private function nullableIntValue(array $row, array $keys): ?int
    {
        $value = $this->value($row, $keys);

        return $value === null ? null : (int) $value;
    }

    private function floatValue(array $row, array $keys, float $default = 0): float
    {
        return (float) $this->value($row, $keys, $default);
    }
}
