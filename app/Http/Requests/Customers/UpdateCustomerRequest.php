<?php

namespace App\Http\Requests\Customers;

use App\Models\Customer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customer = $this->route('customer');
        $customerId = $customer instanceof Customer ? $customer->id : $customer;

        return [
            'whatsapp_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'whatsapp_number')
                    ->where('merchant_id', $this->user()->merchant_id)
                    ->ignore($this->route('customer')),
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'addresses' => ['array'],
            'addresses.*.id' => [
                'nullable',
                'uuid',
                Rule::exists('addresses', 'id')->where('customer_id', $customerId),
            ],
            'addresses.*.label' => ['nullable', 'string', 'max:255'],
            'addresses.*.full_name' => ['nullable', 'string', 'max:255'],
            'addresses.*.phone' => ['nullable', 'string', 'max:255'],
            'addresses.*.line1' => ['nullable', 'string', 'max:255'],
            'addresses.*.line2' => ['nullable', 'string', 'max:255'],
            'addresses.*.city' => ['nullable', 'string', 'max:255'],
            'addresses.*.country' => ['nullable', 'string', 'max:2'],
            'addresses.*.is_default' => ['boolean'],
        ];
    }
}
