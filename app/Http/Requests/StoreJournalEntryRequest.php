<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-journal-entries');
    }

    public function rules(): array
    {
        return [
            'journal_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'entry_type' => ['nullable', 'string', 'in:manual,sales,purchase,adjustment'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:accounts,id'],
            'lines.*.description' => ['nullable', 'string'],
            'lines.*.debit_amount' => ['required', 'numeric', 'min:0'],
            'lines.*.credit_amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $lines = $this->input('lines', []);

            // Validate each line has either debit or credit (not both, not neither)
            foreach ($lines as $index => $line) {
                $debit = floatval($line['debit_amount'] ?? 0);
                $credit = floatval($line['credit_amount'] ?? 0);

                if ($debit > 0 && $credit > 0) {
                    $validator->errors()->add("lines.{$index}", "Line cannot have both debit and credit amounts.");
                }

                if ($debit == 0 && $credit == 0) {
                    $validator->errors()->add("lines.{$index}", "Line must have either debit or credit amount.");
                }
            }

            // Validate balance if action is 'post'
            if ($this->input('action') === 'post') {
                $totalDebit = collect($lines)->sum('debit_amount');
                $totalCredit = collect($lines)->sum('credit_amount');

                if ($totalDebit != $totalCredit) {
                    $validator->errors()->add('lines', 'Total debit must equal total credit untuk posting.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'journal_date.required' => 'Tanggal jurnal wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'lines.required' => 'Minimal 2 baris jurnal diperlukan.',
            'lines.min' => 'Minimal 2 baris jurnal diperlukan.',
            'lines.*.account_id.required' => 'Akun wajib dipilih.',
            'lines.*.account_id.exists' => 'Akun tidak valid.',
        ];
    }
}
