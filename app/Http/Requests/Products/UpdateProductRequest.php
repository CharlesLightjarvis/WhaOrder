<?php

namespace App\Http\Requests\Products;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        return [
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('merchant_id', $this->user()->merchant_id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'keep_image_ids' => ['array'],
            'keep_image_ids.*' => ['integer'],
            'images' => ['array'],
            'images.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'variants' => ['array'],
            'variants.*.id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')->where('product_id', $this->route('product')?->id),
            ],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.images' => ['array'],
            'variants.*.images.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'variants.*.keep_image_ids' => ['array'],
            'variants.*.keep_image_ids.*' => ['integer'],
        ];
    }
}
