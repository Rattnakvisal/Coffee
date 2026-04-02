<?php

namespace App\Http\Controllers\Cashier;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends BaseCashierController
{
    public function addToCart(Request $request): RedirectResponse|JsonResponse
    {
        if ($attendanceGuard = $this->ensureAttendanceChecked($request)) {
            return $attendanceGuard;
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
            'size' => ['required', Rule::in(self::PRODUCT_SIZES)],
            'sugar' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $product = Product::query()
            ->active()
            ->findOrFail((int) $validated['product_id']);

        $qty = (int) ($validated['qty'] ?? 1);
        $size = strtolower((string) $validated['size']);
        $sugar = (int) ($validated['sugar'] ?? 50);

        if (! $product->isSizeActive($size)) {
            return $this->validationFailureResponse(
                $request,
                'Selected size is currently inactive for this product.',
                'size'
            );
        }

        $itemKey = $this->makeCartItemKey($product->id, $size, $sugar);
        $cart = $this->getCart($request);
        $currentQty = (int) ($cart[$itemKey]['qty'] ?? 0);

        $cart[$itemKey] = [
            'product_id' => $product->id,
            'size' => $size,
            'sugar' => $sugar,
            'qty' => min($currentQty + $qty, 99),
        ];

        $this->storeCart($request, $cart);

        return $this->respondCartMutation($request);
    }

    public function incrementCartItem(Request $request, string $itemKey): RedirectResponse|JsonResponse
    {
        if ($attendanceGuard = $this->ensureAttendanceChecked($request)) {
            return $attendanceGuard;
        }

        $cart = $this->getCart($request);

        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['qty'] = min(((int) ($cart[$itemKey]['qty'] ?? 0)) + 1, 99);
            $this->storeCart($request, $cart);
        }

        return $this->respondCartMutation($request);
    }

    public function decrementCartItem(Request $request, string $itemKey): RedirectResponse|JsonResponse
    {
        if ($attendanceGuard = $this->ensureAttendanceChecked($request)) {
            return $attendanceGuard;
        }

        $cart = $this->getCart($request);

        if (isset($cart[$itemKey])) {
            $currentQty = (int) ($cart[$itemKey]['qty'] ?? 0);

            if ($currentQty <= 1) {
                unset($cart[$itemKey]);
            } else {
                $cart[$itemKey]['qty'] = $currentQty - 1;
            }

            $this->storeCart($request, $cart);
        }

        return $this->respondCartMutation($request);
    }

    public function placeOrder(Request $request): RedirectResponse|JsonResponse
    {
        if ($attendanceGuard = $this->ensureAttendanceChecked($request)) {
            return $attendanceGuard;
        }

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(self::PAYMENT_METHODS)],
            'amount_received' => ['nullable', 'numeric', 'min:0'],
        ]);

        $cartState = $this->buildCartState($request);

        if ($cartState['items']->isEmpty()) {
            return $this->orderFailureResponse($request, 'Cannot place order because cart is empty.');
        }

        $paymentMethod = (string) $validated['payment_method'];
        $total = (float) $cartState['total'];
        $amountReceived = (float) ($validated['amount_received'] ?? 0);

        if ($paymentMethod === 'cash' && $amountReceived < $total) {
            return $this->orderFailureResponse(
                $request,
                'Amount received must be greater than or equal to total.'
            );
        }

        if ($paymentMethod !== 'cash') {
            $amountReceived = $total;
        }

        $changeAmount = max($amountReceived - $total, 0.0);
        $orderNumber = $this->generateOrderNumber();

        DB::transaction(function () use (
            $request,
            $cartState,
            $orderNumber,
            $paymentMethod,
            $amountReceived,
            $changeAmount
        ): void {
            $orderPayload = $this->buildOrderPayload(
                $request,
                $cartState,
                $orderNumber,
                $paymentMethod,
                $amountReceived,
                $changeAmount
            );

            $orderId = (int) DB::table('orders')->insertGetId($orderPayload);

            DB::table('order_items')->insert(
                $this->buildOrderItemRows($cartState['items'], $orderId)
            );

            $this->recordInventoryMoneyIn($request, $cartState['total'], $orderNumber, $paymentMethod);
        });

        $request->session()->forget(self::CART_SESSION_KEY);
        $refreshedCartState = $this->buildCartState($request);

        if ($this->wantsJson($request)) {
            return $this->successJsonResponse('Order placed successfully.', [
                'order_number' => $orderNumber,
                'order_total' => $total,
                'amount_received' => $amountReceived,
                'change_amount' => $changeAmount,
                'cart_html' => $this->renderCartHtml($refreshedCartState),
            ]);
        }

        return redirect()
            ->route('cashier.index')
            ->with('status', 'Order '.$orderNumber.' placed successfully.');
    }
}
