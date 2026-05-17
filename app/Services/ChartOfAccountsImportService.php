<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ChartOfAccountsImportService
{
    private const TYPES = ['asset', 'liability', 'equity', 'revenue', 'expense'];

    private const CATEGORIES = [
        'current_asset',
        'fixed_asset',
        'current_liability',
        'long_term_liability',
        'equity',
        'operating_revenue',
        'other_revenue',
        'operating_expense',
        'other_expense',
    ];

    private const TYPE_MAP = [
        'cash_bank' => 'asset',
        'cash' => 'asset',
        'bank' => 'asset',
        'accounts_receivable' => 'asset',
        'inventory' => 'asset',
        'fixed_asset' => 'asset',
        'other_asset' => 'asset',
        'accounts_payable' => 'liability',
        'credit_card' => 'liability',
        'other_current_liability' => 'liability',
        'long_term_liability' => 'liability',
        'equity' => 'equity',
        'income' => 'revenue',
        'revenue' => 'revenue',
        'cost_of_goods_sold' => 'expense',
        'expense' => 'expense',
        'other_expense' => 'expense',
    ];

    public function preview(array $payload): array
    {
        $startedAt = microtime(true);
        $rows = $this->parse($payload);
        $mappedRows = $this->mapRows($rows);
        $validatedRows = $this->validateRows($mappedRows);

        return $this->summary($validatedRows, $startedAt, false);
    }

    public function import(array $payload): array
    {
        $startedAt = microtime(true);
        $rows = $this->parse($payload);
        $mappedRows = $this->mapRows($rows);
        $validatedRows = $this->validateRows($mappedRows);
        $imported = 0;

        DB::transaction(function () use (&$validatedRows, &$imported) {
            $accountsByCode = Account::withTrashed()->get()->keyBy('code');

            foreach ($validatedRows as $index => $row) {
                if (!$row['valid']) {
                    continue;
                }

                $parentId = null;

                if ($row['data']['parent_code']) {
                    $parentId = $accountsByCode->get($row['data']['parent_code'])?->id;
                }

                $account = Account::withTrashed()->updateOrCreate(
                    ['code' => $row['data']['code']],
                    [
                        'name' => $row['data']['name'],
                        'type' => $row['data']['type'],
                        'category' => $row['data']['category'],
                        'parent_id' => $parentId,
                        'level' => $row['data']['level'],
                        'is_header' => $row['data']['is_header'],
                        'is_active' => $row['data']['is_active'],
                        'description' => $row['data']['description'],
                        'balance' => $row['data']['balance'],
                    ]
                );

                if ($account->trashed()) {
                    $account->restore();
                }

                $accountsByCode->put($account->code, $account);
                $validatedRows[$index]['account_id'] = $account->id;
                $imported++;
            }
        });

        $result = $this->summary($validatedRows, $startedAt, true);
        $result['imported'] = $imported;

        return $result;
    }

    private function parse(array $payload): array
    {
        $rows = $payload['accounts'] ?? $payload['data'] ?? $payload['rows'] ?? null;

        if (!is_array($rows)) {
            throw new InvalidArgumentException('Payload import harus memiliki array accounts, data, atau rows.');
        }

        return array_values($rows);
    }

    private function mapRows(array $rows): array
    {
        return collect($rows)->map(function (array $row, int $index) {
            $code = $this->stringValue($row, ['code', 'account_no', 'account_number', 'no_akun', 'number']);
            $type = $this->mapType($this->stringValue($row, ['type', 'account_type', 'jenis_akun']));
            $category = $this->nullableStringValue($row, ['category', 'account_category', 'kategori']);
            $parentCode = $this->nullableStringValue($row, ['parent_code', 'parent_account_no', 'parent_account_number', 'parent']);

            return [
                'row_number' => $index + 1,
                'source' => $row,
                'data' => [
                    'code' => $code,
                    'name' => $this->stringValue($row, ['name', 'account_name', 'nama_akun']),
                    'type' => $type,
                    'category' => $category ? Str::snake(Str::lower($category)) : $this->defaultCategory($type),
                    'parent_code' => $parentCode,
                    'parent_id' => null,
                    'level' => (int) ($row['level'] ?? $row['tingkat'] ?? ($parentCode ? 2 : 1)),
                    'is_header' => $this->booleanValue($row, ['is_header', 'header', 'is_parent'], false),
                    'is_active' => !$this->booleanValue($row, ['suspended', 'inactive', 'is_suspended'], false),
                    'description' => $this->nullableStringValue($row, ['description', 'notes', 'keterangan']),
                    'balance' => (float) ($row['balance'] ?? $row['opening_balance'] ?? $row['saldo'] ?? 0),
                ],
                'valid' => true,
                'errors' => [],
            ];
        })->all();
    }

    private function validateRows(array $rows): array
    {
        $seen = [];
        $knownCodes = Account::pluck('code')->flip();
        $sourceCodes = collect($rows)->pluck('data.code')->filter()->flip();

        foreach ($rows as $index => $row) {
            $errors = [];
            $data = $row['data'];

            if (!$data['code'] || strlen($data['code']) > 20) {
                $errors[] = 'Kode akun wajib diisi dan maksimal 20 karakter.';
            }

            if (!$data['name']) {
                $errors[] = 'Nama akun wajib diisi.';
            }

            if (!in_array($data['type'], self::TYPES, true)) {
                $errors[] = 'Tipe akun tidak valid.';
            }

            if ($data['category'] && !in_array($data['category'], self::CATEGORIES, true)) {
                $errors[] = 'Kategori akun tidak valid.';
            }

            if (isset($seen[$data['code']])) {
                $errors[] = 'Kode akun duplikat dalam payload.';
            }

            if ($data['parent_code']) {
                if ($data['parent_code'] === $data['code']) {
                    $errors[] = 'Parent akun tidak boleh sama dengan kode akun.';
                }

                if (!$sourceCodes->has($data['parent_code']) && !$knownCodes->has($data['parent_code'])) {
                    $errors[] = 'Parent akun tidak ditemukan.';
                }
            }

            if ($this->hasCycle($data['code'], $rows)) {
                $errors[] = 'Relasi parent membentuk siklus.';
            }

            $seen[$data['code']] = true;
            $rows[$index]['valid'] = count($errors) === 0;
            $rows[$index]['errors'] = $errors;
        }

        return $rows;
    }

    private function hasCycle(?string $code, array $rows): bool
    {
        if (!$code) {
            return false;
        }

        $parents = collect($rows)->mapWithKeys(fn ($row) => [$row['data']['code'] => $row['data']['parent_code']])->all();
        $visited = [];
        $current = $code;

        while ($current && isset($parents[$current])) {
            if (isset($visited[$current])) {
                return true;
            }

            $visited[$current] = true;
            $current = $parents[$current];
        }

        return false;
    }

    private function summary(array $rows, float $startedAt, bool $executed): array
    {
        $errors = collect($rows)
            ->filter(fn ($row) => !$row['valid'])
            ->map(fn ($row) => [
                'row_number' => $row['row_number'],
                'code' => $row['data']['code'],
                'errors' => $row['errors'],
            ])
            ->values()
            ->all();

        return [
            'executed' => $executed,
            'total' => count($rows),
            'imported' => 0,
            'valid' => collect($rows)->where('valid', true)->count(),
            'skipped' => count($errors),
            'errors' => $errors,
            'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'accounts' => collect($rows)->pluck('data')->values()->all(),
        ];
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

    private function value(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        return null;
    }

    private function booleanValue(array $row, array $keys, bool $default): bool
    {
        $value = $this->value($row, $keys);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function mapType(?string $type): ?string
    {
        if (!$type) {
            return null;
        }

        $normalized = Str::snake(Str::lower($type));

        return self::TYPE_MAP[$normalized] ?? $normalized;
    }

    private function defaultCategory(?string $type): ?string
    {
        return match ($type) {
            'asset' => 'current_asset',
            'liability' => 'current_liability',
            'equity' => 'equity',
            'revenue' => 'operating_revenue',
            'expense' => 'operating_expense',
            default => null,
        };
    }
}
