<?php

namespace App\Http\Requests;

use App\Models\FiscalPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFiscalPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-fiscal-periods');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'period_type' => ['required', Rule::in(['monthly', 'quarterly', 'yearly'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check for overlapping periods
            $overlap = FiscalPeriod::where('fiscal_year', $this->fiscal_year)
                ->where(function($q) {
                    $q->whereBetween('start_date', [$this->start_date, $this->end_date])
                      ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                      ->orWhere(function($q2) {
                          $q2->where('start_date', '<=', $this->start_date)
                             ->where('end_date', '>=', $this->end_date);
                      });
                })
                ->first();

            if ($overlap) {
                $validator->errors()->add('start_date', "Periode tumpang tindih dengan periode {$overlap->name}.");
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama periode wajib diisi.',
            'period_type.required' => 'Tipe periode wajib dipilih.',
            'period_type.in' => 'Tipe periode tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal akhir wajib diisi.',
            'end_date.after' => 'Tanggal akhir harus setelah tanggal mulai.',
            'fiscal_year.required' => 'Tahun fiskal wajib diisi.',
            'fiscal_year.integer' => 'Tahun fiskal harus berupa angka.',
            'fiscal_year.min' => 'Tahun fiskal minimal 2000.',
            'fiscal_year.max' => 'Tahun fiskal maksimal 2100.',
        ];
    }
}
