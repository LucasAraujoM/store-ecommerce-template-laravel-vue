<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends StoreProductRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        return [
            ...parent::rules(),
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('products', 'slug')->ignore($product),
            ],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', Rule::exists('product_images', 'id')->where('product_id', $product->id)],
        ];
    }
}
