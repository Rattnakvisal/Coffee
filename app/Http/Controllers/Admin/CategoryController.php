<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $categories = Category::query()
            ->with('creator:id,name,first_name,last_name')
            ->withCount('products')
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where(function ($categoryQuery) use ($search): void {
                        $categoryQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                },
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $slug = $this->uniqueSlug($validated['name']);

        Category::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_active' => ($validated['is_active'] ?? '1') === '1',
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('alert', [
                'icon' => 'success',
                'title' => 'Category Added',
                'text' => 'Category created successfully.',
            ]);
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $slug = Str::slug($validated['name']);
        $slug = $slug !== '' ? $slug : 'category';
        $baseSlug = $slug;
        $counter = 1;

        while (
            Category::query()
                ->where('slug', $slug)
                ->whereKeyNot($category->id)
                ->exists()
        ) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_active' => ($validated['is_active'] ?? '1') === '1',
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('alert', [
                'icon' => 'success',
                'title' => 'Category Updated',
                'text' => 'Category updated successfully.',
            ]);
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with('alert', [
                    'icon' => 'error',
                    'title' => 'Delete Blocked',
                    'text' => 'Category has products. Reassign products first.',
                ]);
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('alert', [
                'icon' => 'success',
                'title' => 'Category Deleted',
                'text' => 'Category removed successfully.',
            ]);
    }

    private function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : 'category';
        $counter = 1;

        while (Category::query()->where('slug', $slug)->exists()) {
            $counter++;
            $slug = ($baseSlug !== '' ? $baseSlug : 'category') . '-' . $counter;
        }

        return $slug;
    }
}
