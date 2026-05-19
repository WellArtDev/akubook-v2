<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HistoricalTransactionsImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'historical_transactions' => ['nullable', 'array'],
            'transactions' => ['nullable', 'array'],
            'data' => ['nullable', 'array'],
            'rows' => ['nullable', 'array'],
        ];
    }
}
