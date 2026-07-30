<?php

namespace App\Http\Requests\Settings;

use App\Support\Currencies;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MerchantUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', 'max:255'],
            'whatsapp_admin_number' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', Rule::in(array_keys(Currencies::list()))],
            'timezone' => ['required', 'string', 'timezone'],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
        ];
    }
}
