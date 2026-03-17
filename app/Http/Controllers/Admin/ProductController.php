<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $products = Product::query()
            ->with('category')
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where(function ($productQuery) use ($search): void {
                        $productQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhereHas('category', function ($categoryQuery) use ($search): void {
                                $categoryQuery->where('name', 'like', "%{$search}%");
                            });
                    });
                },
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::query()->active()->orderBy('name')->get(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query): void {
                    $query->where('is_active', true);
                }),
            ],
            'description' => ['nullable', 'string', 'max:1500'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug !== '' ? $baseSlug : 'product';
        $counter = 1;

        while (Product::query()->where('slug', $slug)->exists()) {
            $counter++;
            $slug = ($baseSlug !== '' ? $baseSlug : 'product') . '-' . $counter;
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'category_id' => $validated['category_id'],
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'price' => $validated['price'],
            'is_active' => ($validated['is_active'] ?? '1') === '1',
        ]);

        return redirect()
            ->route('admin.products.index')
            ->with('alert', [
                'icon' => 'success',
                'title' => 'Product Added',
                'text' => 'Product has been created and is available in cashier.',
            ]);
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product->load('category'),
            'categories' => Category::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query): void {
                    $query->where('is_active', true);
                }),
            ],
            'description' => ['nullable', 'string', 'max:1500'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $slug = Str::slug($validated['name']);
        $slug = $slug !== '' ? $slug : 'product';
        $baseSlug = $slug;
        $counter = 1;

        while (
            Product::query()
                ->where('slug', $slug)
                ->whereKeyNot($product->id)
                ->exists()
        ) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        $imagePath = $product->image_path;

        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'category_id' => $validated['category_id'],
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'price' => $validated['price'],
            'is_active' => ($validated['is_active'] ?? '1') === '1',
        ]);

        return redirect()
            ->route('admin.products.index')
            ->with('alert', [
                'icon' => 'success',
                'title' => 'Product Updated',
                'text' => 'Product updated successfully.',
            ]);
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('alert', [
                'icon' => 'success',
                'title' => 'Product Deleted',
                'text' => 'Product has been removed successfully.',
            ]);
    }
}
