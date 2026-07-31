<?php

namespace App\Http\Requests\Products;

use App\Models\Product;
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
        $hasVariants = count((array) $this->input('variants', [])) > 0;
        $product = $this->route('product');
        $productId = $product instanceof Product ? $product->id : $product;

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
            'keep_image_ids' => ['array'],
            'keep_image_ids.*' => ['uuid'],
            'images' => [Rule::prohibitedIf($hasVariants), 'array'],
            'images.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'variants' => ['array'],
            'variants.*.id' => [
                'nullable',
                'uuid',
                Rule::exists('product_variants', 'id')->where('product_id', $productId),
            ],
            'variants.*.name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.stock' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.images' => ['array'],
            'variants.*.images.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'variants.*.keep_image_ids' => ['array'],
            'variants.*.keep_image_ids.*' => ['uuid'],
        ];
    }
}
