<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'category' => $request->string('category')->toString(),
            'status' => $request->string('status')->toString(),
        ];

        $products = Product::query()
            ->with(['category:id,name,slug', 'images', 'variants'])
            ->withSum('variants', 'stock')
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $term = '%'.$filters['q'].'%';
                $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', $term)
                        ->orWhere('slug', 'like', $term);
                });
            })
            ->when($filters['category'] !== '', function ($query) use ($filters): void {
                $query->whereHas('category', fn ($query) => $query->where('slug', $filters['category']));
            })
            ->when($filters['status'] === 'active', fn ($query) => $query->where('is_active', true))
            ->when($filters['status'] === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('admin/Products/Index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/Products/Form', [
            'product' => null,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $product = Product::create($this->productAttributes($data));

        $this->storeImages($product, $request->file('images', []));
        $this->syncVariants($product, $data['variants']);

        return redirect()
            ->route('admin.products.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Producto creado.',
            ]);
    }

    public function edit(Product $product): Response
    {
        $product->load(['images', 'variants' => fn ($query) => $query->orderBy('size')->orderBy('color')]);

        return Inertia::render('admin/Products/Form', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        $product->update($this->productAttributes($data));

        $this->removeImages($product, $data['remove_images'] ?? []);
        $this->storeImages($product, $request->file('images', []));
        $this->syncVariants($product, $data['variants']);

        return redirect()
            ->route('admin.products.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Producto actualizado.',
            ]);
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images as $image) {
            $this->deleteImageFile($image);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Producto eliminado.',
            ]);
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        $wasPrimary = $image->is_primary;
        $this->deleteImageFile($image);
        $image->delete();

        if ($wasPrimary) {
            $product->images()->first()?->update(['is_primary' => true]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Imagen eliminada.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function productAttributes(array $data): array
    {
        return [
            'category_id' => $data['category_id'] ?? null,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'price_cents' => (int) round(((float) $data['price']) * 100),
            'compare_at_price_cents' => filled($data['compare_at_price'] ?? null)
                ? (int) round(((float) $data['compare_at_price']) * 100)
                : null,
            'is_active' => (bool) $data['is_active'],
            'is_featured' => (bool) $data['is_featured'],
        ];
    }

    /**
     * @param  array<int, UploadedFile>  $images
     */
    private function storeImages(Product $product, array $images): void
    {
        if ($images === []) {
            return;
        }

        $hasPrimary = $product->images()->where('is_primary', true)->exists();
        $sortOrder = (int) $product->images()->max('sort_order');

        foreach ($images as $index => $image) {
            $path = $image->store('products', 'public');

            $product->images()->create([
                'path' => $path,
                'alt' => $product->name,
                'sort_order' => $sortOrder + $index + 1,
                'is_primary' => ! $hasPrimary && $index === 0,
            ]);
        }
    }

    /**
     * @param  array<int, int|string>  $imageIds
     */
    private function removeImages(Product $product, array $imageIds): void
    {
        if ($imageIds === []) {
            return;
        }

        $images = $product->images()->whereKey($imageIds)->get();

        foreach ($images as $image) {
            $wasPrimary = $image->is_primary;
            $this->deleteImageFile($image);
            $image->delete();

            if ($wasPrimary) {
                $product->images()->first()?->update(['is_primary' => true]);
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $variants
     */
    private function syncVariants(Product $product, array $variants): void
    {
        $keptIds = [];

        foreach ($variants as $variant) {
            $attributes = [
                'size' => trim((string) $variant['size']),
                'color' => trim((string) $variant['color']),
                'color_hex' => filled($variant['color_hex'] ?? null) ? $variant['color_hex'] : null,
                'sku' => filled($variant['sku'] ?? null) ? trim((string) $variant['sku']) : null,
                'stock' => (int) $variant['stock'],
                'price_cents' => filled($variant['price'] ?? null)
                    ? (int) round(((float) $variant['price']) * 100)
                    : null,
                'is_active' => (bool) ($variant['is_active'] ?? true),
            ];

            if (! empty($variant['id'])) {
                $model = $product->variants()->findOrFail($variant['id']);
                $model->update($attributes);
            } else {
                $model = $product->variants()->create($attributes);
            }

            $keptIds[] = $model->id;
        }

        $product->variants()->whereKeyNot($keptIds)->delete();
    }

    private function deleteImageFile(ProductImage $image): void
    {
        if (! str_starts_with($image->path, 'http')) {
            Storage::disk('public')->delete($image->path);
        }
    }
}
