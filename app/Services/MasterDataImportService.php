<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MasterDataImportService
{
    private const CUSTOMER_TYPES = ['individual', 'company'];

    private const ITEM_TYPES = ['goods', 'service'];

    public function preview(array $payload): array
    {
        $startedAt = microtime(true);
        $rows = $this->parse($payload);
        $mapped = $this->mapRows($rows);
        $validated = $this->validateRows($mapped);

        return $this->summary($validated, $startedAt, false);
    }

    public function import(array $payload): array
    {
        $startedAt = microtime(true);
        $rows = $this->parse($payload);
        $mapped = $this->mapRows($rows);
        $validated = $this->validateRows($mapped);
        $importedByEntity = [
            'customers' => 0,
            'suppliers' => 0,
            'items' => 0,
        ];

        DB::transaction(function () use (&$validated, &$importedByEntity) {
            foreach ($validated as $index => $row) {
                if (!$row['valid']) {
                    continue;
                }

                $entity = $row['entity'];
                $data = $row['data'];

                if ($entity === 'customers') {
                    $model = Customer::withTrashed()->updateOrCreate(
                        ['code' => $data['code']],
                        $data
                    );
                } elseif ($entity === 'suppliers') {
                    $model = Supplier::withTrashed()->updateOrCreate(
                        ['supplier_code' => $data['supplier_code']],
                        $data
                    );
                } else {
                    $model = Item::updateOrCreate(
                        ['code' => $data['code']],
                        $data
                    );
                }

                if (method_exists($model, 'trashed') && $model->trashed()) {
                    $model->restore();
                }

                $validated[$index]['record_id'] = $model->id;
                $importedByEntity[$entity]++;
            }
        });

        $result = $this->summary($validated, $startedAt, true);
        $result['imported_by_entity'] = $importedByEntity;
        $result['imported'] = array_sum($importedByEntity);

        return $result;
    }

    private function parse(array $payload): array
    {
        $rows = [
            'customers' => $payload['customers'] ?? [],
            'suppliers' => $payload['suppliers'] ?? [],
            'items' => $payload['items'] ?? [],
        ];

        if (($payload['data'] ?? null) && is_array($payload['data'])) {
            $rows['customers'] = $payload['data']['customers'] ?? $rows['customers'];
            $rows['suppliers'] = $payload['data']['suppliers'] ?? $rows['suppliers'];
            $rows['items'] = $payload['data']['items'] ?? $rows['items'];
        }

        if (($payload['rows'] ?? null) && is_array($payload['rows'])) {
            $rows['customers'] = $payload['rows']['customers'] ?? $rows['customers'];
            $rows['suppliers'] = $payload['rows']['suppliers'] ?? $rows['suppliers'];
            $rows['items'] = $payload['rows']['items'] ?? $rows['items'];
        }

        foreach (['customers', 'suppliers', 'items'] as $entity) {
            if (!is_array($rows[$entity])) {
                throw new InvalidArgumentException('Payload '.$entity.' harus berupa array.');
            }
        }

        if (count($rows['customers']) === 0 && count($rows['suppliers']) === 0 && count($rows['items']) === 0) {
            throw new InvalidArgumentException('Payload master data kosong.');
        }

        return $rows;
    }

    private function mapRows(array $rows): array
    {
        $result = [];

        foreach ($rows['customers'] as $row) {
            $result[] = [
                'entity' => 'customers',
                'source' => $row,
                'data' => [
                    'code' => $this->stringValue($row, ['code', 'customer_code', 'no_pelanggan']),
                    'name' => $this->stringValue($row, ['name', 'customer_name', 'nama']),
                    'category' => $this->nullableStringValue($row, ['category', 'customer_type', 'type', 'jenis_pelanggan']) ?: 'retail',
                    'tax_type' => $this->mapTaxType($this->nullableStringValue($row, ['tax_type', 'jenis_pajak'])),
                    'email' => $this->nullableStringValue($row, ['email']),
                    'phone' => $this->nullableStringValue($row, ['phone', 'mobile', 'telp']) ?: '',
                    'website' => $this->nullableStringValue($row, ['website']),
                    'tax_id' => $this->nullableStringValue($row, ['tax_id', 'npwp']),
                    'credit_limit' => $this->floatValue($row, ['credit_limit', 'limit_kredit'], 0),
                    'payment_terms' => $this->intValue($row, ['payment_terms', 'payment_terms_days', 'termin_hari', 'payment_term_days'], 0),
                    'outstanding_balance' => $this->floatValue($row, ['outstanding_balance', 'saldo_piutang'], 0),
                    'is_active' => $this->boolValue($row, ['is_active', 'active'], true),
                    'notes' => $this->nullableStringValue($row, ['notes', 'keterangan']),
                ],
                'valid' => true,
                'errors' => [],
            ];
        }

        foreach ($rows['suppliers'] as $row) {
            $result[] = [
                'entity' => 'suppliers',
                'source' => $row,
                'data' => [
                    'supplier_code' => $this->stringValue($row, ['supplier_code', 'code', 'vendor_code']),
                    'name' => $this->stringValue($row, ['name', 'supplier_name', 'nama']),
                    'category' => $this->nullableStringValue($row, ['category', 'kategori']),
                    'tax_id' => $this->nullableStringValue($row, ['tax_id', 'npwp']),
                    'tax_type' => $this->mapTaxType($this->nullableStringValue($row, ['tax_type', 'jenis_pajak'])),
                    'payment_terms' => $this->nullableStringValue($row, ['payment_terms', 'termin']),
                    'phone' => $this->nullableStringValue($row, ['phone', 'mobile', 'telp']),
                    'email' => $this->nullableStringValue($row, ['email']),
                    'website' => $this->nullableStringValue($row, ['website']),
                    'notes' => $this->nullableStringValue($row, ['notes', 'keterangan']),
                    'delivery_rating' => $this->floatValue($row, ['delivery_rating'], 0),
                    'quality_rating' => $this->floatValue($row, ['quality_rating'], 0),
                    'total_purchase_amount' => $this->floatValue($row, ['total_purchase_amount'], 0),
                    'last_purchase_date' => $this->nullableStringValue($row, ['last_purchase_date']),
                ],
                'valid' => true,
                'errors' => [],
            ];
        }

        foreach ($rows['items'] as $row) {
            $result[] = [
                'entity' => 'items',
                'source' => $row,
                'data' => [
                    'code' => $this->stringValue($row, ['code', 'item_code', 'kode_barang']),
                    'name' => $this->stringValue($row, ['name', 'item_name', 'nama']),
                    'description' => $this->nullableStringValue($row, ['description', 'keterangan']),
                    'item_type' => $this->mapItemType($this->nullableStringValue($row, ['item_type', 'type', 'jenis_item'])),
                    'unit' => $this->stringValue($row, ['unit', 'satuan']),
                    'purchase_price' => $this->floatValue($row, ['purchase_price', 'harga_beli'], 0),
                    'selling_price' => $this->floatValue($row, ['selling_price', 'harga_jual'], 0),
                    'is_active' => $this->boolValue($row, ['is_active', 'active'], true),
                ],
                'valid' => true,
                'errors' => [],
            ];
        }

        foreach ($result as $index => $row) {
            $result[$index]['row_number'] = $index + 1;
        }

        return $result;
    }

    private function validateRows(array $rows): array
    {
        $seen = [
            'customers' => [],
            'suppliers' => [],
            'items' => [],
        ];

        $known = [
            'customers' => Customer::withTrashed()->pluck('code')->filter()->flip(),
            'suppliers' => Supplier::withTrashed()->pluck('supplier_code')->filter()->flip(),
            'items' => Item::query()->pluck('code')->filter()->flip(),
        ];

        foreach ($rows as $index => $row) {
            $entity = $row['entity'];
            $data = $row['data'];
            $errors = [];

            $code = $entity === 'suppliers' ? $data['supplier_code'] : $data['code'];

            if (!$code) {
                $errors[] = 'Kode wajib diisi.';
            }

            if ($code && strlen($code) > 50) {
                $errors[] = 'Kode maksimal 50 karakter.';
            }

            if (!$data['name']) {
                $errors[] = 'Nama wajib diisi.';
            }

            if (isset($seen[$entity][$code])) {
                $errors[] = 'Kode duplikat dalam payload entitas sama.';
            }

            $seen[$entity][$code] = true;

            if ($entity === 'customers') {
                if ($data['payment_terms'] < 0) {
                    $errors[] = 'Payment terms tidak boleh negatif.';
                }
            }

            if ($entity === 'suppliers') {
                if (!$data['tax_type'] || !in_array($data['tax_type'], ['pkp', 'non_pkp'], true)) {
                    $errors[] = 'Tax type supplier tidak valid.';
                }
            }

            if ($entity === 'items') {
                if (!in_array($data['item_type'], self::ITEM_TYPES, true)) {
                    $errors[] = 'Item type tidak valid.';
                }

                if (!$data['unit']) {
                    $errors[] = 'Unit wajib diisi.';
                }
            }

            if ($code && $known[$entity]->has($code)) {
                // allowed for update
            }

            $rows[$index]['valid'] = count($errors) === 0;
            $rows[$index]['errors'] = $errors;
        }

        return $rows;
    }

    private function summary(array $rows, float $startedAt, bool $executed): array
    {
        $errors = collect($rows)
            ->filter(fn ($row) => !$row['valid'])
            ->map(fn ($row) => [
                'entity' => $row['entity'],
                'row_number' => $row['row_number'],
                'code' => $row['entity'] === 'suppliers' ? $row['data']['supplier_code'] : $row['data']['code'],
                'errors' => $row['errors'],
            ])
            ->values()
            ->all();

        $perEntity = collect($rows)->groupBy('entity')->map(function ($group) {
            return [
                'total' => $group->count(),
                'valid' => $group->where('valid', true)->count(),
                'skipped' => $group->where('valid', false)->count(),
            ];
        })->all();

        return [
            'executed' => $executed,
            'total' => count($rows),
            'valid' => collect($rows)->where('valid', true)->count(),
            'imported' => 0,
            'skipped' => count($errors),
            'per_entity' => $perEntity,
            'errors' => $errors,
            'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
        ];
    }

    private function mapCustomerType(?string $value): string
    {
        if (!$value) {
            return 'company';
        }

        return Str::snake(Str::lower($value));
    }

    private function mapItemType(?string $value): string
    {
        if (!$value) {
            return 'goods';
        }

        return Str::snake(Str::lower($value));
    }

    private function mapTaxType(?string $value): string
    {
        if (!$value) {
            return 'non_pkp';
        }

        return Str::snake(Str::lower($value));
    }

    private function value(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        return null;
    }

    private function stringValue(array $row, array $keys): string
    {
        return trim((string) ($this->value($row, $keys) ?? ''));
    }

    private function nullableStringValue(array $row, array $keys): ?string
    {
        $value = $this->stringValue($row, $keys);

        return $value === '' ? null : $value;
    }

    private function floatValue(array $row, array $keys, float $default): float
    {
        $value = $this->value($row, $keys);

        return is_numeric($value) ? (float) $value : $default;
    }

    private function intValue(array $row, array $keys, int $default): int
    {
        $value = $this->value($row, $keys);

        return is_numeric($value) ? (int) $value : $default;
    }

    private function boolValue(array $row, array $keys, bool $default): bool
    {
        $value = $this->value($row, $keys);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
