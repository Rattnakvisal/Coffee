<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    private const CAMBODIA_TIMEZONE = 'Asia/Phnom_Penh';

    public function index(Request $request): View
    {
        $now = now();
        $preset = $this->normalizePreset((string) $request->query('preset', 'month'));
        $type = $this->normalizeType((string) $request->query('type', 'all'));
        $payment = $this->normalizePayment((string) $request->query('payment', 'all'));
        $search = trim((string) $request->query('search', ''));

        [$start, $end, $rangeLabel] = $this->resolveDateRange($preset, $now);
        $orderDateColumn = $this->orderDateColumn();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();

        $moneyInQuery = $this->paidOrdersQuery()
            ->whereBetween($orderDateColumn, [$start, $end]);

        if ($payment !== 'all' && Schema::hasColumn('orders', 'payment_method')) {
            $moneyInQuery->whereRaw("LOWER(COALESCE(payment_method, 'unknown')) = ?", [$payment]);
        }

        if ($search !== '') {
            $this->applyMoneyInSearch($moneyInQuery, $search);
        }

        $moneyOutQuery = InventoryTransaction::query()
            ->moneyOut()
            ->whereBetween('happened_at', [$start, $end]);

        if ($search !== '') {
            $this->applyMoneyOutSearch($moneyOutQuery, $search);
        }

        $moneyInTotal = (float) (clone $moneyInQuery)->sum($this->incomeAmountColumn());
        $moneyOutTotal = (float) (clone $moneyOutQuery)->sum('amount');
        $moneyInCount = (int) (clone $moneyInQuery)->count();
        $moneyOutCount = (int) (clone $moneyOutQuery)->count();
        $userColumns = $this->userSelectColumns();

        $moneyInToday = (float) $this->paidOrdersQuery()
            ->whereBetween($orderDateColumn, [$todayStart, $todayEnd])
            ->sum($this->incomeAmountColumn());
        $moneyOutToday = (float) InventoryTransaction::query()
            ->moneyOut()
            ->whereBetween('happened_at', [$todayStart, $todayEnd])
            ->sum('amount');

        $moneyInEntries = collect();
        if ($type !== InventoryTransaction::TYPE_MONEY_OUT) {
            $moneyInEntries = (clone $moneyInQuery)
                ->with([
                    'cashier' => function ($query) use ($userColumns): void {
                        $query->select($userColumns);
                    },
                ])
                ->orderByDesc($orderDateColumn)
                ->orderByDesc('id')
                ->get()
                ->map(function (Order $order) use ($orderDateColumn): array {
                    $happenedAt = $this->toCarbonInstance($order->{$orderDateColumn} ?? $order->created_at);
                    $amountColumn = $this->incomeAmountColumn();
                    $amount = (float) ($order->{$amountColumn} ?? 0);
                    $grossAmount = (float) ($order->subtotal ?? $amount);
                    $discountAmount = max((float) ($order->discount ?? 0), 0.0);
                    $cashierName = $this->formatUserName($order->cashier, 'Cashier');
                    $paymentMethod = strtoupper((string) ($order->payment_method ?? 'UNKNOWN'));
                    $orderNumber = (string) ($order->order_number ?? '-');
                    $note = $discountAmount > 0
                        ? sprintf(
                            'Order payment received. Discount applied: $%.2f (gross: $%.2f).',
                            $discountAmount,
                            $grossAmount,
                        )
                        : 'Order payment received.';

                    return [
                        'id' => 'order-' . $order->id,
                        'type' => InventoryTransaction::TYPE_MONEY_IN,
                        'amount' => $amount,
                        'money_in' => $amount,
                        'money_out' => 0.0,
                        'payment_method' => $paymentMethod,
                        'reference' => $orderNumber,
                        'note' => $note,
                        'actor_name' => $cashierName,
                        'happened_at_local' => $this->formatDateLocal($happenedAt),
                        'sort_timestamp' => $happenedAt->timestamp,
                    ];
                });
        }

        $moneyOutEntries = collect();
        if ($type !== InventoryTransaction::TYPE_MONEY_IN) {
            $moneyOutEntries = (clone $moneyOutQuery)
                ->with([
                    'creator' => function ($query) use ($userColumns): void {
                        $query->select($userColumns);
                    },
                ])
                ->orderByDesc('happened_at')
                ->orderByDesc('id')
                ->get()
                ->map(function (InventoryTransaction $transaction): array {
                    $happenedAt = $this->toCarbonInstance($transaction->happened_at ?? $transaction->created_at);
                    $amount = (float) ($transaction->amount ?? 0);

                    return [
                        'id' => 'out-' . $transaction->id,
                        'type' => InventoryTransaction::TYPE_MONEY_OUT,
                        'amount' => $amount,
                        'money_in' => 0.0,
                        'money_out' => $amount,
                        'payment_method' => '-',
                        'reference' => 'OUT-' . str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT),
                        'note' => (string) ($transaction->note ?? 'No note provided.'),
                        'actor_name' => $this->formatUserName($transaction->creator, 'System'),
                        'happened_at_local' => $this->formatDateLocal($happenedAt),
                        'sort_timestamp' => $happenedAt->timestamp,
                    ];
                });
        }

        $entries = $moneyInEntries
            ->concat($moneyOutEntries)
            ->sortByDesc('sort_timestamp')
            ->values();

        $perPage = 5;
        $currentPage = max((int) $request->query('page', 1), 1);
        $offset = ($currentPage - 1) * $perPage;
        $pageItems = $entries->slice($offset, $perPage)->values();

        $paginatedEntries = new LengthAwarePaginator(
            $pageItems,
            $entries->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        return view('admin.inventory.index', [
            'preset' => $preset,
            'type' => $type,
            'payment' => $payment,
            'search' => $search,
            'rangeLabel' => $rangeLabel,
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
            'moneyInTotal' => $moneyInTotal,
            'moneyOutTotal' => $moneyOutTotal,
            'balanceTotal' => $moneyInTotal - $moneyOutTotal,
            'moneyInToday' => $moneyInToday,
            'moneyOutToday' => $moneyOutToday,
            'moneyInCount' => $moneyInCount,
            'moneyOutCount' => $moneyOutCount,
            'entries' => $paginatedEntries,
            'paymentOptions' => $this->paymentOptions(),
            'typeOptions' => [
                ['value' => 'all', 'label' => 'All Types'],
                ['value' => InventoryTransaction::TYPE_MONEY_IN, 'label' => 'Income'],
                ['value' => InventoryTransaction::TYPE_MONEY_OUT, 'label' => 'Outgoing'],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => [
                'required',
                Rule::in([
                    InventoryTransaction::TYPE_MONEY_OUT,
                ]),
            ],
            'amount' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string', 'max:500'],
            'happened_at' => ['nullable', 'date'],
        ]);

        $userId = (int) ($request->user()?->id ?? 0);
        $happenedAt = filled($validated['happened_at'] ?? null)
            ? Carbon::parse((string) $validated['happened_at'], self::CAMBODIA_TIMEZONE)
                ->timezone(config('app.timezone'))
            : now();

        InventoryTransaction::query()->create([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'note' => $validated['note'] ?? null,
            'happened_at' => $happenedAt,
            'created_by' => $userId > 0 ? $userId : null,
        ]);

        return redirect()
            ->route('admin.inventory.index')
            ->with('alert', [
                'icon' => 'success',
                'title' => 'Inventory Updated',
                'text' => 'Outgoing entry has been saved.',
            ]);
    }

    private function applyMoneyInSearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $builder) use ($search): void {
            $builder->where('order_number', 'like', "%{$search}%");

            if (Schema::hasColumn('orders', 'payment_method')) {
                $builder->orWhere('payment_method', 'like', "%{$search}%");
            }

            $builder->orWhereHas('cashier', function (Builder $cashierQuery) use ($search): void {
                $cashierQuery->where('name', 'like', "%{$search}%");

                if (Schema::hasColumn('users', 'first_name')) {
                    $cashierQuery->orWhere('first_name', 'like', "%{$search}%");
                }

                if (Schema::hasColumn('users', 'last_name')) {
                    $cashierQuery->orWhere('last_name', 'like', "%{$search}%");
                }
            });
        });
    }

    private function applyMoneyOutSearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $builder) use ($search): void {
            $builder->where('note', 'like', "%{$search}%")
                ->orWhereHas('creator', function (Builder $creatorQuery) use ($search): void {
                    $creatorQuery->where('name', 'like', "%{$search}%");

                    if (Schema::hasColumn('users', 'first_name')) {
                        $creatorQuery->orWhere('first_name', 'like', "%{$search}%");
                    }

                    if (Schema::hasColumn('users', 'last_name')) {
                        $creatorQuery->orWhere('last_name', 'like', "%{$search}%");
                    }
                });
        });
    }

    private function normalizePreset(string $value): string
    {
        $normalized = str($value)->lower()->squish()->value();

        return in_array($normalized, ['today', 'week', 'month', 'all'], true)
            ? $normalized
            : 'month';
    }

    private function normalizeType(string $value): string
    {
        $normalized = str($value)->lower()->squish()->value();

        return in_array($normalized, ['all', InventoryTransaction::TYPE_MONEY_IN, InventoryTransaction::TYPE_MONEY_OUT], true)
            ? $normalized
            : 'all';
    }

    private function normalizePayment(string $value): string
    {
        $normalized = str($value)->lower()->squish()->value();

        return $normalized !== '' ? $normalized : 'all';
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolveDateRange(string $preset, Carbon $now): array
    {
        return match ($preset) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today'],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'This Week'],
            'all' => [Carbon::create(2000, 1, 1, 0, 0, 0), $now->copy()->endOfDay(), 'All Time'],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month'],
        };
    }

    private function incomeAmountColumn(): string
    {
        if (Schema::hasColumn('orders', 'total')) {
            return 'total';
        }

        if (Schema::hasColumn('orders', 'subtotal')) {
            return 'subtotal';
        }

        return 'total';
    }

    /**
     * @return Collection<int, string>
     */
    private function paymentOptions(): Collection
    {
        if (! Schema::hasColumn('orders', 'payment_method')) {
            return collect();
        }

        return $this->paidOrdersQuery()
            ->selectRaw("LOWER(COALESCE(payment_method, 'unknown')) as value")
            ->pluck('value')
            ->filter(fn($value): bool => trim((string) $value) !== '')
            ->map(fn($value): string => (string) $value)
            ->unique()
            ->sort()
            ->values();
    }

    private function orderDateColumn(): string
    {
        return Schema::hasColumn('orders', 'placed_at') ? 'placed_at' : 'created_at';
    }

    private function paidOrdersQuery(): Builder
    {
        $query = Order::query();

        if (Schema::hasColumn('orders', 'payment_status')) {
            $query->whereRaw("LOWER(COALESCE(payment_status, 'paid')) = ?", ['paid']);
        }

        return $query;
    }

    private function formatUserName(?User $user, string $fallback): string
    {
        if ($user === null) {
            return $fallback;
        }

        $display = trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''));

        if ($display !== '') {
            return $display;
        }

        $name = trim((string) ($user->name ?? ''));

        return $name !== '' ? $name : $fallback;
    }

    /**
     * @return array<int, string>
     */
    private function userSelectColumns(): array
    {
        $columns = ['id', 'name'];

        if (Schema::hasColumn('users', 'first_name')) {
            $columns[] = 'first_name';
        }

        if (Schema::hasColumn('users', 'last_name')) {
            $columns[] = 'last_name';
        }

        return $columns;
    }

    private function formatDateLocal(Carbon $value): string
    {
        return $value
            ->copy()
            ->timezone(self::CAMBODIA_TIMEZONE)
            ->format('d/m/Y H:i');
    }

    private function toCarbonInstance(mixed $value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value === null || $value === '') {
            return now();
        }

        return Carbon::parse((string) $value);
    }
}
