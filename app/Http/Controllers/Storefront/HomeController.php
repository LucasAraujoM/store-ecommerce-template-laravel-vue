<?php

namespace App\Http\Controllers\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $categories = Category::query()
            ->active()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->get();

        $featured = Product::query()
            ->active()
            ->featured()
            ->with(['category:id,name,slug', 'images', 'variants' => fn ($query) => $query->where('is_active', true)])
            ->latest()
            ->take(8)
            ->get();

        $newArrivals = Product::query()
            ->active()
            ->with(['category:id,name,slug', 'images', 'variants' => fn ($query) => $query->where('is_active', true)])
            ->latest()
            ->take(4)
            ->get();

        return Inertia::render('store/Home', [
            'categories' => $categories,
            'featured' => $featured,
            'newArrivals' => $newArrivals,
        ]);
    }
}
