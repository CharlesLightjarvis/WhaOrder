<?php

namespace App\Http\Requests\Products;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
        $hasVariants = count((array) $this->input('variants', [])) > 0;

        return [
            'category_id' => [
                'nullable',
                'uuid',
                Rule::exists('categories', 'id')->where('merchant_id', $this->user()->merchant_id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => [Rule::requiredIf(! $hasVariants), 'nullable', 'numeric', 'min:0'],
            'stock' => [Rule::requiredIf(! $hasVariants), 'nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'images' => [Rule::prohibitedIf($hasVariants), 'array'],
            'images.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'variants' => ['array'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.images' => ['array'],
            'variants.*.images.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ];
    }
}
