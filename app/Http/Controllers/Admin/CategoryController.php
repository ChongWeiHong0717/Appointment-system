<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\ImageStorageService;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Category::class);
        $categories = $request->user()->business->categories()
            ->withCount('services')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        Gate::authorize('create', Category::class);

        return view('admin.categories.create');
    }

    public function store(CategoryRequest $request, SlugService $slugs, ImageStorageService $images): RedirectResponse
    {
        $business = $request->user()->business;
        $data = $request->validated();
        unset($data['image']);
        $data['business_id'] = $business->id;
        $data['slug'] = $slugs->forBusiness(Category::class, $business, $data['name']);
        $data['image_path'] = $images->replace($request->file('image'), null, "businesses/{$business->id}/categories");
        $business->categories()->create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Request $request, int $category): View
    {
        $category = $request->user()->business->categories()->findOrFail($category);
        Gate::authorize('update', $category);

        return view('admin.categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, int $category, SlugService $slugs, ImageStorageService $images): RedirectResponse
    {
        $business = $request->user()->business;
        $category = $business->categories()->findOrFail($category);
        Gate::authorize('update', $category);
        $data = $request->validated();
        unset($data['image']);
        $data['slug'] = $slugs->forBusiness(Category::class, $business, $data['name'], $category->id);
        $data['image_path'] = $images->replace($request->file('image'), $category->image_path, "businesses/{$business->id}/categories");
        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Request $request, int $category, ImageStorageService $images): RedirectResponse
    {
        $category = $request->user()->business->categories()->findOrFail($category);
        Gate::authorize('delete', $category);

        if ($category->services()->exists()) {
            return back()->withErrors(['category' => 'Move or delete this category’s services before deleting it.']);
        }

        $images->delete($category->image_path);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }
}
