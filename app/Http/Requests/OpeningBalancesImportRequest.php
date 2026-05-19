<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpeningBalancesImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opening_balances' => ['nullable', 'array'],
            'balances' => ['nullable', 'array'],
            'fiscal_period_id' => ['nullable', 'integer'],
            'balance_date' => ['nullable', 'date'],
            'data' => ['nullable', 'array'],
            'rows' => ['nullable', 'array'],
        ];
    }
}
