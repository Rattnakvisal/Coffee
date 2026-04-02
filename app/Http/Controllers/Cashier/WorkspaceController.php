<?php

namespace App\Http\Controllers\Cashier;

use App\Models\CashierAttendance;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends BaseCashierController
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));

        $categories = Category::query()
            ->active()
            ->orderBy('name')
            ->get();

        $searchSuggestions = $this->buildSearchSuggestions($categories);

        $products = Product::query()
            ->active()
            ->with('category')
            ->when(
                $category !== '',
                function (Builder $query) use ($category): void {
                    $query->whereHas('category', function (Builder $categoryQuery) use ($category): void {
                        $categoryQuery->where('slug', $category);
                    });
                }
            )
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $query->where(function (Builder $productQuery) use ($search): void {
                        $productQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhereHas('category', function (Builder $categoryQuery) use ($search): void {
                                $categoryQuery->where('name', 'like', "%{$search}%");
                            });
                    });
                }
            )
            ->orderBy('name')
            ->get();

        $cartState = $this->buildCartState($request);

        $todayAttendance = CashierAttendance::query()
            ->where('cashier_id', (int) ($request->user()?->id ?? 0))
            ->whereDate('attended_on', now()->toDateString())
            ->orderBy('checked_in_at')
            ->first();

        return view('cashier.index', [
            'products' => $products,
            'categories' => $categories,
            'category' => $category,
            'search' => $search,
            'searchSuggestions' => $searchSuggestions,
            'cartItems' => $cartState['items'],
            'cartSubtotal' => $cartState['subtotal'],
            'cartDiscount' => $cartState['discount'],
            'cartTotal' => $cartState['total'],
            'todayAttendance' => $todayAttendance,
        ]);
    }

    public function goToDashboard(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login.form', ['role' => 'admin'])
            ->with('status', 'Please sign in with an admin account to open the dashboard.');
    }
}
