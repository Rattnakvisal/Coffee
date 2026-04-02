<?php

namespace App\Http\Controllers\Cashier;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class HistoryController extends BaseCashierController
{
    public function history(Request $request): View
    {
        $period = $this->normalizePeriod((string) $request->query('period', 'day'));
        $search = trim((string) $request->query('search', ''));
        $selectedPayment = $this->normalizeHistoryFilter((string) $request->query('payment', 'all'));
        $selectedStatus = $this->normalizeHistoryFilter((string) $request->query('status', 'all'));
        $dateColumn = $this->orderDateColumn();

        [$start, $end, $periodLabel] = $this->periodRange($period);

        $baseHistoryQuery = $this->cashierOrdersQuery($request)
            ->whereBetween($dateColumn, [$start, $end]);

        $this->applyHistoryOrderFilters($baseHistoryQuery, $selectedPayment, $selectedStatus);

        $paymentOptions = $this->historyPaymentOptions(clone $baseHistoryQuery);
        $statusOptions = $this->historyStatusOptions(clone $baseHistoryQuery);

        $ordersQuery = (clone $baseHistoryQuery)
            ->with('items:id,order_id,product_name,qty')
            ->withCount('items')
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $query->where(function (Builder $orderQuery) use ($search): void {
                        $orderQuery
                            ->where('order_number', 'like', "%{$search}%")
                            ->orWhere('payment_method', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%");
                    });
                }
            );

        $ordersCount = (int) (clone $ordersQuery)->count();
        $revenue = (float) (clone $ordersQuery)->sum('total');
        $averageOrder = $ordersCount > 0 ? $revenue / $ordersCount : 0.0;
        $itemsSold = $this->calculateItemsSold(
            $request,
            $dateColumn,
            $start,
            $end,
            $search,
            $selectedPayment,
            $selectedStatus
        );
        $latestOrderAt = (clone $ordersQuery)->max($dateColumn);

        $orders = (clone $ordersQuery)
            ->orderByDesc($dateColumn)
            ->paginate(12)
            ->withQueryString();

        $cartState = $this->buildCartState($request);

        return view('cashier.history', [
            'period' => $period,
            'periodLabel' => $periodLabel,
            'search' => $search,
            'orders' => $orders,
            'ordersCount' => $ordersCount,
            'revenue' => $revenue,
            'averageOrder' => $averageOrder,
            'itemsSold' => $itemsSold,
            'selectedPayment' => $selectedPayment,
            'selectedStatus' => $selectedStatus,
            'paymentOptions' => $paymentOptions,
            'statusOptions' => $statusOptions,
            'latestOrderAt' => $latestOrderAt,
            'cartItems' => $cartState['items'],
            'cartSubtotal' => $cartState['subtotal'],
            'cartDiscount' => $cartState['discount'],
            'cartTotal' => $cartState['total'],
        ]);
    }
}
