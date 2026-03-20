<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $products = Product::query()
            ->with([
                'category',
                'creator:id,name,first_name,last_name',
            ])
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
        $validated = $this->validateProductPayload($request);

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
            'created_by' => $request->user()?->id,
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'price' => $this->resolvePrimaryPrice($validated),
            'price_small' => $validated['price_small'],
            'price_medium' => $validated['price_medium'],
            'price_large' => $validated['price_large'],
            'is_small_active' => ($validated['is_small_active'] ?? '1') === '1',
            'is_medium_active' => ($validated['is_medium_active'] ?? '1') === '1',
            'is_large_active' => ($validated['is_large_active'] ?? '1') === '1',
            'discount_percent' => $validated['discount_percent'] ?? 0,
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
        $validated = $this->validateProductPayload($request);

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
            'price' => $this->resolvePrimaryPrice($validated),
            'price_small' => $validated['price_small'],
            'price_medium' => $validated['price_medium'],
            'price_large' => $validated['price_large'],
            'is_small_active' => ($validated['is_small_active'] ?? '1') === '1',
            'is_medium_active' => ($validated['is_medium_active'] ?? '1') === '1',
            'is_large_active' => ($validated['is_large_active'] ?? '1') === '1',
            'discount_percent' => $validated['discount_percent'] ?? 0,
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

    /**
     * @return array<string, mixed>
     */
    private function validateProductPayload(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(function ($query): void {
                    $query->where('is_active', true);
                }),
            ],
            'description' => ['nullable', 'string', 'max:1500'],
            'price_small' => ['required', 'numeric', 'min:0'],
            'price_medium' => ['required', 'numeric', 'min:0'],
            'price_large' => ['required', 'numeric', 'min:0'],
            'is_small_active' => ['nullable', Rule::in(['0', '1'])],
            'is_medium_active' => ['nullable', Rule::in(['0', '1'])],
            'is_large_active' => ['nullable', Rule::in(['0', '1'])],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $isSmallActive = (string) $request->input('is_small_active', '1') === '1';
            $isMediumActive = (string) $request->input('is_medium_active', '1') === '1';
            $isLargeActive = (string) $request->input('is_large_active', '1') === '1';

            if (! $isSmallActive && ! $isMediumActive && ! $isLargeActive) {
                $validator->errors()->add('size_availability', 'At least one size must be active.');
            }
        });

        return $validator->validate();
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function resolvePrimaryPrice(array $validated): float
    {
        if (($validated['is_small_active'] ?? '1') === '1') {
            return (float) $validated['price_small'];
        }

        if (($validated['is_medium_active'] ?? '1') === '1') {
            return (float) $validated['price_medium'];
        }

        return (float) $validated['price_large'];
    }
}
