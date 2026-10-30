<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'category' => $request->string('category')->toString(),
            'size' => $request->string('size')->toString(),
            'color' => $request->string('color')->toString(),
            'sort' => $request->string('sort', 'recent')->toString(),
            'min' => $request->filled('min') ? $request->integer('min') : null,
            'max' => $request->filled('max') ? $request->integer('max') : null,
        ];

        $products = Product::query()
            ->active()
            ->with([
                'category:id,name,slug',
                'images',
                'variants' => fn ($query) => $query->where('is_active', true),
            ])
            ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                $term = '%'.$filters['q'].'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->where('name', 'like', $term)
                        ->orWhere('short_description', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($filters['category'] !== '', function (Builder $query) use ($filters): void {
                $query->whereHas('category', fn (Builder $query) => $query->where('slug', $filters['category']));
            })
            ->when($filters['size'] !== '', function (Builder $query) use ($filters): void {
                $query->whereHas('variants', function (Builder $query) use ($filters): void {
                    $query->where('is_active', true)->where('size', $filters['size']);
                });
            })
            ->when($filters['color'] !== '', function (Builder $query) use ($filters): void {
                $query->whereHas('variants', function (Builder $query) use ($filters): void {
                    $query->where('is_active', true)->where('color', $filters['color']);
                });
            })
            ->when($filters['min'] !== null, fn (Builder $query) => $query->where('price_cents', '>=', $filters['min'] * 100))
            ->when($filters['max'] !== null, fn (Builder $query) => $query->where('price_cents', '<=', $filters['max'] * 100))
            ->when($filters['sort'] === 'price_asc', fn (Builder $query) => $query->orderBy('price_cents')->orderBy('id'))
            ->when($filters['sort'] === 'price_desc', fn (Builder $query) => $query->orderByDesc('price_cents')->orderByDesc('id'))
            ->when($filters['sort'] === 'name', fn (Builder $query) => $query->orderBy('name')->orderBy('id'))
            ->when($filters['sort'] === 'recent', fn (Builder $query) => $query->latest()->orderByDesc('id'))
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->active()
            ->withCount(['products' => fn (Builder $query) => $query->active()])
            ->get();

        $sizes = ProductVariant::query()
            ->where('is_active', true)
            ->whereHas('product', fn (Builder $query) => $query->active())
            ->distinct()
            ->orderBy('size')
            ->pluck('size');

        $colors = ProductVariant::query()
            ->where('is_active', true)
            ->whereHas('product', fn (Builder $query) => $query->active())
            ->select('color', 'color_hex')
            ->distinct()
            ->orderBy('color')
            ->get();

        return Inertia::render('store/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'sizes' => $sizes,
            'colors' => $colors,
            'filters' => $filters,
        ]);
    }

    public function show(Product $product): Response
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'category:id,name,slug',
            'images',
            'variants' => fn ($query) => $query->where('is_active', true)->orderBy('size')->orderBy('color'),
        ]);

        $related = Product::query()
            ->active()
            ->whereKeyNot($product->id)
            ->when(
                $product->category_id,
                fn (Builder $query) => $query->where('category_id', $product->category_id),
                fn (Builder $query) => $query->where('is_featured', true),
            )
            ->with(['category:id,name,slug', 'images', 'variants' => fn ($query) => $query->where('is_active', true)])
            ->latest()
            ->take(4)
            ->get();

        return Inertia::render('store/Products/Show', [
            'product' => $product,
            'related' => $related,
        ]);
    }
}
