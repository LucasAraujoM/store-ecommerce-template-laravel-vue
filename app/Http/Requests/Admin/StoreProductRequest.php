<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],

            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.size' => ['required', 'string', 'max:20'],
            'variants.*.color' => ['required', 'string', 'max:40'],
            'variants.*.color_hex' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'variants.*.sku' => ['nullable', 'string', 'max:64'],
            'variants.*.stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'variants.*.is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug')
                ? Str::slug($this->string('slug')->toString())
                : Str::slug($this->string('name')->toString()),
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $variants = $this->input('variants', []);

                if (! is_array($variants)) {
                    return;
                }

                $combinations = [];
                $skus = [];

                foreach ($variants as $index => $variant) {
                    if (! is_array($variant)) {
                        continue;
                    }

                    $combination = mb_strtolower(trim(($variant['size'] ?? '').'|'.($variant['color'] ?? '')));

                    if (isset($combinations[$combination])) {
                        $validator->errors()->add(
                            "variants.{$index}.size",
                            'Esta combinación de talle y color está repetida.',
                        );
                    }

                    $combinations[$combination] = true;

                    $sku = trim((string) ($variant['sku'] ?? ''));

                    if ($sku === '') {
                        continue;
                    }

                    $query = ProductVariant::query()->where('sku', $sku);

                    if (! empty($variant['id'])) {
                        $query->whereKeyNot($variant['id']);
                    }

                    if ($query->exists()) {
                        $validator->errors()->add(
                            "variants.{$index}.sku",
                            'Este SKU ya está en uso.',
                        );
                    }

                    if (isset($skus[mb_strtolower($sku)])) {
                        $validator->errors()->add(
                            "variants.{$index}.sku",
                            'Este SKU está repetido en el formulario.',
                        );
                    }

                    $skus[mb_strtolower($sku)] = true;
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'variants.required' => 'Agregá al menos una variante con talle y color.',
        ];
    }
}
