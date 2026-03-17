<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CashierController extends Controller
{
    private const CART_SESSION_KEY = 'cashier_cart';

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));

        $categories = Category::query()->active()->orderBy('name')->get();

        $products = Product::query()
            ->active()
            ->with('category')
            ->when(
                $category !== '',
                function ($query) use ($category): void {
                    $query->whereHas('category', function ($categoryQuery) use ($category): void {
                        $categoryQuery->where('slug', $category);
                    });
                },
            )
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
            ->orderBy('name')
            ->get();

        $cartState = $this->buildCartState($request);

        return view('cashier.index', [
            'products' => $products,
            'categories' => $categories,
            'category' => $category,
            'search' => $search,
            'cartItems' => $cartState['items'],
            'cartSubtotal' => $cartState['subtotal'],
            'cartDiscount' => $cartState['discount'],
            'cartTotal' => $cartState['total'],
        ]);
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
            'size' => ['required', 'in:small,medium,large'],
            'sugar' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $product = Product::query()->active()->findOrFail((int) $validated['product_id']);
        $qty = (int) ($validated['qty'] ?? 1);
        $size = strtolower((string) $validated['size']);
        $sugar = (int) ($validated['sugar'] ?? 50);
        $itemKey = $this->makeCartItemKey($product->id, $size, $sugar);

        $cart = $this->getCart($request);
        $currentQty = (int) ($cart[$itemKey]['qty'] ?? 0);

        $cart[$itemKey] = [
            'product_id' => $product->id,
            'size' => $size,
            'sugar' => $sugar,
            'qty' => min($currentQty + $qty, 99),
        ];

        $request->session()->put(self::CART_SESSION_KEY, $cart);

        return back();
    }

    public function incrementCartItem(Request $request, string $itemKey): RedirectResponse
    {
        $cart = $this->getCart($request);

        if (! isset($cart[$itemKey])) {
            return back();
        }

        $currentQty = (int) ($cart[$itemKey]['qty'] ?? 0);
        $cart[$itemKey]['qty'] = min($currentQty + 1, 99);

        $request->session()->put(self::CART_SESSION_KEY, $cart);

        return back();
    }

    public function decrementCartItem(Request $request, string $itemKey): RedirectResponse
    {
        $cart = $this->getCart($request);

        if (! isset($cart[$itemKey])) {
            return back();
        }

        $currentQty = (int) ($cart[$itemKey]['qty'] ?? 0);

        if ($currentQty <= 1) {
            unset($cart[$itemKey]);
        } else {
            $cart[$itemKey]['qty'] = $currentQty - 1;
        }

        $request->session()->put(self::CART_SESSION_KEY, $cart);

        return back();
    }

    /**
     * @return array<string, array{
     *   product_id: int,
     *   size: string,
     *   sugar: int,
     *   qty: int
     * }>
     */
    private function getCart(Request $request): array
    {
        $rawCart = $request->session()->get(self::CART_SESSION_KEY, []);
        $cart = [];

        foreach ((array) $rawCart as $key => $value) {
            if (is_numeric($key) && is_numeric($value)) {
                $legacyProductId = (int) $key;
                $legacyQty = (int) $value;

                if ($legacyProductId <= 0 || $legacyQty <= 0) {
                    continue;
                }

                $legacySize = 'small';
                $legacySugar = 50;
                $legacyKey = $this->makeCartItemKey($legacyProductId, $legacySize, $legacySugar);

                $cart[$legacyKey] = [
                    'product_id' => $legacyProductId,
                    'size' => $legacySize,
                    'sugar' => $legacySugar,
                    'qty' => min($legacyQty, 99),
                ];

                continue;
            }

            if (! is_array($value)) {
                continue;
            }

            $productId = (int) ($value['product_id'] ?? 0);
            $size = strtolower((string) ($value['size'] ?? 'small'));
            $sugar = (int) ($value['sugar'] ?? 50);
            $qty = (int) ($value['qty'] ?? 0);

            if ($productId <= 0 || ! in_array($size, ['small', 'medium', 'large'], true) || $qty <= 0) {
                continue;
            }

            $normalizedSugar = max(0, min(100, $sugar));
            $itemKey = $this->makeCartItemKey($productId, $size, $normalizedSugar);

            $cart[$itemKey] = [
                'product_id' => $productId,
                'size' => $size,
                'sugar' => $normalizedSugar,
                'qty' => min($qty, 99),
            ];
        }

        return $cart;
    }

    private function makeCartItemKey(int $productId, string $size, int $sugar): string
    {
        return $productId . '-' . $size . '-' . $sugar;
    }

    /**
     * @return array{
     *   items: Collection<int, array{
     *     item_key: string,
     *     product_id: int,
     *     name: string,
     *     image_path: string|null,
     *     size: string,
     *     sugar: int,
     *     qty: int,
     *     unit_price: float,
     *     line_total: float
     *   }>,
     *   subtotal: float,
     *   discount: float,
     *   total: float
     * }
     */
    private function buildCartState(Request $request): array
    {
        $cart = $this->getCart($request);
        $productIds = collect($cart)
            ->pluck('product_id')
            ->unique()
            ->values()
            ->all();

        if ($productIds === []) {
            return [
                'items' => collect(),
                'subtotal' => 0.0,
                'discount' => 0.0,
                'total' => 0.0,
            ];
        }

        $products = Product::query()
            ->active()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $items = collect($cart)
            ->map(function (array $entry, string $itemKey) use ($products): ?array {
                $productId = (int) ($entry['product_id'] ?? 0);
                /** @var Product|null $product */
                $product = $products->get($productId);

                if (! $product) {
                    return null;
                }

                $qty = (int) ($entry['qty'] ?? 1);
                $size = (string) ($entry['size'] ?? 'small');
                $sugar = (int) ($entry['sugar'] ?? 50);
                $unitPrice = (float) $product->price;
                $lineTotal = $unitPrice * $qty;

                return [
                    'item_key' => $itemKey,
                    'product_id' => $product->id,
                    'name' => (string) $product->name,
                    'image_path' => $product->image_path,
                    'size' => $size,
                    'sugar' => $sugar,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            })
            ->filter()
            ->values();

        $validCart = $items
            ->mapWithKeys(fn (array $item): array => [
                $item['item_key'] => [
                    'product_id' => $item['product_id'],
                    'size' => $item['size'],
                    'sugar' => $item['sugar'],
                    'qty' => $item['qty'],
                ],
            ])
            ->all();

        if ($validCart !== $cart) {
            $request->session()->put(self::CART_SESSION_KEY, $validCart);
        }

        $subtotal = (float) $items->sum('line_total');
        $discount = 0.0;
        $total = max($subtotal - $discount, 0.0);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
        ];
    }
}
