<?php

namespace App\Services;

use App\Models\CashierAttendance;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AdminHeaderService
{
    private const SESSION_NOTIFICATION_HIDDEN_KEYS = 'admin_notifications_hidden_keys';
    private const SESSION_NOTIFICATION_HIDDEN_BEFORE = 'admin_notifications_hidden_before';

    /**
     * @return Collection<int, array{label: string, value: string, type: string, meta: string}>
     */
    public function buildSearchSuggestions(): Collection
    {
        return collect([
            ['label' => 'Dashboard', 'value' => 'dashboard', 'type' => 'Shortcut', 'meta' => 'Overview'],
            ['label' => 'Products', 'value' => 'products', 'type' => 'Shortcut', 'meta' => 'Manage products'],
            ['label' => 'Categories', 'value' => 'categories', 'type' => 'Shortcut', 'meta' => 'Manage categories'],
            ['label' => 'Users', 'value' => 'users', 'type' => 'Shortcut', 'meta' => 'Manage staff'],
            ['label' => 'Inventory', 'value' => 'inventory', 'type' => 'Shortcut', 'meta' => 'Income & outgoing details'],
            ['label' => 'Reports', 'value' => 'reports', 'type' => 'Shortcut', 'meta' => 'Sales analytics'],
            ['label' => 'Attendance', 'value' => 'attendance', 'type' => 'Shortcut', 'meta' => 'Cashier attendance details'],
            ['label' => 'Settings', 'value' => 'settings', 'type' => 'Shortcut', 'meta' => 'Account settings'],
            ['label' => 'Profile', 'value' => 'profile', 'type' => 'Shortcut', 'meta' => 'Profile settings'],
            ['label' => 'Password', 'value' => 'password', 'type' => 'Shortcut', 'meta' => 'Security settings'],
        ])
            ->merge(
                Product::query()
                    ->latest()
                    ->limit(8)
                    ->get(['name'])
                    ->map(fn(Product $product): array => [
                        'label' => (string) $product->name,
                        'value' => (string) $product->name,
                        'type' => 'Product',
                        'meta' => 'Item',
                    ]),
            )
            ->merge(
                Category::query()
                    ->latest()
                    ->limit(8)
                    ->get(['name'])
                    ->map(fn(Category $category): array => [
                        'label' => (string) $category->name,
                        'value' => (string) $category->name,
                        'type' => 'Category',
                        'meta' => 'Group',
                    ]),
            )
            ->merge(
                User::query()
                    ->latest()
                    ->limit(8)
                    ->get(['name', 'email'])
                    ->map(fn(User $user): array => [
                        'label' => (string) $user->name,
                        'value' => (string) $user->name,
                        'type' => 'User',
                        'meta' => (string) $user->email,
                    ]),
            )
            ->filter(fn(array $item): bool => trim((string) ($item['value'] ?? '')) !== '')
            ->unique(fn(array $item): string => strtolower($item['value'] . '|' . $item['type']))
            ->values();
    }

    /**
     * @return Collection<int, array{id: int, source: string, title: string, message: string, time: string}>
     */
    public function serializedNotificationsForPanel(Request $request, int $limit = 5): Collection
    {
        return $this->serializeNotifications(
            $this->applySessionRemovalFilters($request, $this->collectNotifications(false, $limit)),
        );
    }

    public function unreadNotificationsCount(Request $request): int
    {
        return (int) $this
            ->applySessionRemovalFilters($request, $this->collectNotifications(true, 1000))
            ->count();
    }

    /**
     * @return Collection<int, array{id: int, source: string, title: string, message: string, time: string, timestamp: int}>
     */
    private function collectNotifications(bool $onlyUnread, int $limit): Collection
    {
        $notifications = collect();

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'admin_notified_at')) {
            $orderDateColumn = Schema::hasColumn('orders', 'placed_at') ? 'placed_at' : 'created_at';
            $orderQuery = Order::query()
                ->with('cashier:id,name,first_name,last_name,email')
                ->orderByDesc($orderDateColumn)
                ->when(
                    Schema::hasColumn('orders', 'admin_removed_at'),
                    fn($query) => $query->whereNull('admin_removed_at'),
                );

            if ($onlyUnread) {
                $orderQuery->whereNull('admin_notified_at');
            }

            $orderRows = $orderQuery
                ->limit($limit)
                ->get();

            $orderNotifications = $orderRows->map(function (Order $order) use ($orderDateColumn): array {
                $orderedAtRaw = $order->{$orderDateColumn};
                $orderedAt = $orderedAtRaw ? Carbon::parse((string) $orderedAtRaw) : now();
                $orderedAtLabel = $orderedAt->format('d/m/Y H:i');
                $cashierName = $this->formatUserDisplayName($order->cashier);
                $paymentLabel = str((string) ($order->payment_method ?? 'cash'))->upper()->value();

                return [
                    'id' => (int) $order->id,
                    'source' => 'order',
                    'title' => 'New Order',
                    'message' => 'Order ' . (string) ($order->order_number ?? '-') . ' by ' . $cashierName .
                        ' total $' . number_format((float) ($order->total ?? 0), 2) . ' via ' . $paymentLabel . '.',
                    'time' => $orderedAtLabel,
                    'timestamp' => $orderedAt->timestamp,
                ];
            });

            $notifications = $notifications->merge($orderNotifications);
        }

        if (Schema::hasTable('cashier_attendances') && Schema::hasColumn('cashier_attendances', 'admin_notified_at')) {
            $attendanceQuery = CashierAttendance::query()
                ->with('cashier:id,name,first_name,last_name,email')
                ->orderByDesc('checked_in_at')
                ->when(
                    Schema::hasColumn('cashier_attendances', 'admin_removed_at'),
                    fn($query) => $query->whereNull('admin_removed_at'),
                );

            if ($onlyUnread) {
                $attendanceQuery->whereNull('admin_notified_at');
            }

            $attendanceRows = $attendanceQuery
                ->limit($limit)
                ->get();

            $attendanceNotifications = $attendanceRows->map(function (CashierAttendance $attendance): array {
                $checkedAt = $attendance->checked_in_at ?? now();
                $checkedAtLabel = $checkedAt->format('d/m/Y H:i');
                $cashierName = $this->formatUserDisplayName($attendance->cashier);

                return [
                    'id' => (int) $attendance->id,
                    'source' => 'attendance',
                    'title' => 'Attendance Update',
                    'message' => $cashierName . ' checked attendance at ' . $checkedAtLabel . '.',
                    'time' => $checkedAtLabel,
                    'timestamp' => $checkedAt->timestamp,
                ];
            });

            $notifications = $notifications->merge($attendanceNotifications);
        }

        return $notifications
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values()
            ->map(function (array $notification): array {
                return [
                    'id' => (int) ($notification['id'] ?? 0),
                    'source' => (string) ($notification['source'] ?? ''),
                    'title' => (string) ($notification['title'] ?? 'Notification'),
                    'message' => (string) ($notification['message'] ?? ''),
                    'time' => (string) ($notification['time'] ?? now()->format('d/m/Y H:i')),
                    'timestamp' => (int) ($notification['timestamp'] ?? now()->timestamp),
                ];
            })
            ->values();
    }

    /**
     * @param Collection<int, array<string, mixed>> $notifications
     * @return Collection<int, array{id: int, source: string, title: string, message: string, time: string}>
     */
    private function serializeNotifications(Collection $notifications): Collection
    {
        return $notifications
            ->map(function (array $notification): array {
                return [
                    'id' => (int) ($notification['id'] ?? 0),
                    'source' => (string) ($notification['source'] ?? ''),
                    'title' => (string) ($notification['title'] ?? 'Notification'),
                    'message' => (string) ($notification['message'] ?? ''),
                    'time' => (string) ($notification['time'] ?? now()->format('d/m/Y H:i')),
                ];
            })
            ->values();
    }

    /**
     * @param Collection<int, array<string, mixed>> $notifications
     * @return Collection<int, array<string, mixed>>
     */
    private function applySessionRemovalFilters(Request $request, Collection $notifications): Collection
    {
        $hiddenKeys = array_flip($this->hiddenNotificationKeys($request));
        $hiddenBeforeTimestamp = $this->hiddenBeforeTimestamp($request);

        return $notifications
            ->filter(function (array $notification) use ($hiddenKeys, $hiddenBeforeTimestamp): bool {
                $source = (string) ($notification['source'] ?? '');
                $id = (int) ($notification['id'] ?? 0);
                $key = $this->notificationKey($source, $id);

                if ($source !== '' && $id > 0 && isset($hiddenKeys[$key])) {
                    return false;
                }

                if ($hiddenBeforeTimestamp === null) {
                    return true;
                }

                $timestamp = (int) ($notification['timestamp'] ?? 0);

                return $timestamp > $hiddenBeforeTimestamp;
            })
            ->values();
    }

    private function formatUserDisplayName(?User $user): string
    {
        if (! $user) {
            return 'Cashier';
        }

        $fullName = trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''));

        if ($fullName !== '') {
            return $fullName;
        }

        $fallbackName = trim((string) ($user->name ?? ''));

        return $fallbackName !== '' ? $fallbackName : 'Cashier';
    }

    /**
     * @return array<int, string>
     */
    private function hiddenNotificationKeys(Request $request): array
    {
        return collect((array) $request->session()->get(self::SESSION_NOTIFICATION_HIDDEN_KEYS, []))
            ->map(fn(mixed $value): string => trim((string) $value))
            ->filter(fn(string $value): bool => $value !== '')
            ->values()
            ->all();
    }

    private function hiddenBeforeTimestamp(Request $request): ?int
    {
        $rawValue = trim((string) $request->session()->get(self::SESSION_NOTIFICATION_HIDDEN_BEFORE, ''));
        if ($rawValue === '') {
            return null;
        }

        try {
            return Carbon::parse($rawValue)->timestamp;
        } catch (\Throwable) {
            return null;
        }
    }

    private function notificationKey(string $source, int $id): string
    {
        return $source . ':' . $id;
    }
}
