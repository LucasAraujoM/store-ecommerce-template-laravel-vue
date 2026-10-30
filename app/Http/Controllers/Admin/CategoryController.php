<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/Categories/Form', [
            'category' => null,
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $category = Category::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'],
            'sort_order' => $data['sort_order'],
            'image' => $request->file('image')?->store('categories', 'public'),
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Categoría creada.',
            ]);
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('admin/Categories/Form', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $image = $category->image;

        if ($request->hasFile('image')) {
            if ($image && ! str_starts_with($image, 'http')) {
                Storage::disk('public')->delete($image);
            }

            $image = $request->file('image')->store('categories', 'public');
        }

        $category->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'],
            'sort_order' => $data['sort_order'],
            'image' => $image,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Categoría actualizada.',
            ]);
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->image && ! str_starts_with($category->image, 'http')) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Categoría eliminada.',
            ]);
    }
}
