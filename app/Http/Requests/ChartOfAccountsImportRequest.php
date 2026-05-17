<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChartOfAccountsImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'accounts' => ['nullable', 'array'],
            'data' => ['nullable', 'array'],
            'rows' => ['nullable', 'array'],
        ];
    }
}
