<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MasterDataImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customers' => ['nullable', 'array'],
            'suppliers' => ['nullable', 'array'],
            'items' => ['nullable', 'array'],
            'data' => ['nullable', 'array'],
            'rows' => ['nullable', 'array'],
        ];
    }
}
