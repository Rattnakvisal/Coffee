<?php

namespace App\Http\Controllers\Cashier;

use App\Models\CashierAttendance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceController extends BaseCashierController
{
    public function attendance(Request $request): View
    {
        $todayDate = now()->toDateString();

        $todayAttendanceByCashier = CashierAttendance::query()
            ->whereDate('attended_on', $todayDate)
            ->get()
            ->keyBy('cashier_id');

        $cashierUsers = $this->cashierUsersQuery()
            ->with('role:id,slug')
            ->orderBy('name')
            ->orderBy('email')
            ->get();

        $cashierRows = $cashierUsers
            ->map(function ($cashier) use ($todayAttendanceByCashier): array {
                return [
                    'cashier' => $cashier,
                    'name' => $this->resolveUserDisplayName($cashier),
                    'email' => (string) ($cashier->email ?? '-'),
                    'todayAttendance' => $todayAttendanceByCashier->get((int) $cashier->id),
                ];
            })
            ->values();

        $attendanceHistory = CashierAttendance::query()
            ->with('cashier:id,name,first_name,last_name,email')
            ->orderByDesc('attended_on')
            ->orderByDesc('checked_in_at')
            ->limit(5)
            ->get();

        $totalCashiers = (int) $cashierRows->count();
        $checkedTodayCount = (int) $cashierRows
            ->filter(fn (array $row): bool => $row['todayAttendance'] !== null)
            ->count();
        $pendingTodayCount = max($totalCashiers - $checkedTodayCount, 0);

        $cartState = $this->buildCartState($request);

        return view('cashier.attendance', [
            'cashierRows' => $cashierRows,
            'attendanceHistory' => $attendanceHistory,
            'totalCashiers' => $totalCashiers,
            'checkedTodayCount' => $checkedTodayCount,
            'pendingTodayCount' => $pendingTodayCount,
            'cartItems' => $cartState['items'],
            'cartSubtotal' => $cartState['subtotal'],
            'cartDiscount' => $cartState['discount'],
            'cartTotal' => $cartState['total'],
        ]);
    }

    public function checkAttendance(Request $request): RedirectResponse|JsonResponse
    {
        $defaultCashierId = (int) ($request->user()?->id ?? 0);
        $cashierId = (int) $request->input('cashier_id', $defaultCashierId);
        $redirectRoute = $request->input('redirect') === 'attendance'
            ? 'cashier.attendance'
            : 'cashier.index';

        if ($cashierId <= 0) {
            return $this->errorResponse(
                $request,
                'Unable to check attendance. Please sign in again.',
                'attendance',
                $redirectRoute
            );
        }

        $cashier = $this->cashierUsersQuery()->find($cashierId);

        if (! $cashier) {
            return $this->errorResponse(
                $request,
                'Selected user is not a cashier account.',
                'attendance',
                $redirectRoute
            );
        }

        $checkedAt = now();

        $attendance = CashierAttendance::query()->firstOrCreate(
            [
                'cashier_id' => $cashierId,
                'attended_on' => $checkedAt->toDateString(),
            ],
            [
                'checked_in_at' => $checkedAt,
                'admin_notified_at' => null,
            ]
        );

        $cashierLabel = $this->resolveUserDisplayName($cashier);
        $checkedTime = $attendance->checked_in_at?->format('H:i') ?? '--:--';

        $message = $attendance->wasRecentlyCreated
            ? $cashierLabel.' attendance checked successfully.'
            : $cashierLabel.' attendance already checked today at '.$checkedTime.'.';

        if ($this->wantsJson($request)) {
            return $this->successJsonResponse($message, [
                'was_recently_created' => $attendance->wasRecentlyCreated,
                'attendance' => [
                    'cashier_id' => (int) $cashier->id,
                    'cashier_name' => $cashierLabel,
                    'cashier_email' => (string) ($cashier->email ?? '-'),
                    'checked_in_at' => $attendance->checked_in_at?->format('H:i:s') ?? '--:--:--',
                    'attended_on' => $attendance->attended_on?->format('d/m/Y') ?? now()->format('d/m/Y'),
                    'is_today' => $attendance->attended_on?->toDateString() === now()->toDateString(),
                ],
                'stats' => $this->attendanceStats(),
            ]);
        }

        return redirect()
            ->route($redirectRoute)
            ->with('status', $message);
    }
}
