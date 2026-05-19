<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('customers', 'name')->ignore($customerId)],
            'category' => ['required', Rule::in(['retail', 'wholesale', 'corporate'])],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'tax_type' => ['required', Rule::in(['pkp', 'non_pkp'])],
            'phone' => ['required', 'string', 'max:50', 'regex:/^[0-9+()\-\s]+$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'payment_terms' => ['required', 'integer', Rule::in([0, 7, 14, 30, 45, 60])],
            'notes' => ['nullable', 'string'],
            'contacts' => ['required', 'array', 'min:1'],
            'contacts.*.id' => ['nullable', 'exists:customer_contacts,id'],
            'contacts.*.name' => ['required', 'string', 'max:255'],
            'contacts.*.position' => ['nullable', 'string', 'max:100'],
            'contacts.*.phone' => ['required', 'string', 'max:50', 'regex:/^[0-9+()\-\s]+$/'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'contacts.*.is_primary' => ['boolean'],
            'addresses' => ['required', 'array', 'min:1'],
            'addresses.*.id' => ['nullable', 'exists:customer_addresses,id'],
            'addresses.*.address_type' => ['required', Rule::in(['billing', 'shipping', 'both'])],
            'addresses.*.street_address' => ['required', 'string'],
            'addresses.*.city' => ['required', 'string', 'max:100'],
            'addresses.*.province' => ['required', 'string', 'max:100'],
            'addresses.*.postal_code' => ['nullable', 'string', 'max:20'],
            'addresses.*.country' => ['required', 'string', 'max:100'],
            'addresses.*.is_default' => ['boolean'],
        ];
    }
}
