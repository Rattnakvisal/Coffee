<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashierAttendance;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $date = trim((string) $request->query('date', ''));
        $todayDate = now()->toDateString();

        $cashierUsers = User::query()
            ->whereHas('role', function (Builder $query): void {
                $query->where('slug', 'cashier');
            })
            ->orderBy('name')
            ->orderBy('email')
            ->get();

        $todayAttendanceByCashier = CashierAttendance::query()
            ->whereDate('attended_on', $todayDate)
            ->get()
            ->keyBy('cashier_id');

        $todayRows = $cashierUsers->map(function (User $cashier) use ($todayAttendanceByCashier): array {
            $attendance = $todayAttendanceByCashier->get((int) $cashier->id);

            return [
                'cashier_id' => (int) $cashier->id,
                'cashier_name' => $this->displayName($cashier),
                'cashier_email' => (string) ($cashier->email ?? '-'),
                'checked_in_at' => $attendance?->checked_in_at,
                'is_checked' => $attendance !== null,
            ];
        })->values();

        $attendanceQuery = CashierAttendance::query()
            ->with('cashier:id,name,first_name,last_name,email')
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $query->whereHas('cashier', function (Builder $cashierQuery) use ($search): void {
                        $cashierQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                },
            )
            ->when(
                $date !== '',
                fn(Builder $query): Builder => $query->whereDate('attended_on', $date),
            )
            ->orderByDesc('attended_on')
            ->orderByDesc('checked_in_at');

        $attendanceRows = $attendanceQuery
            ->paginate(15)
            ->withQueryString();

        $totalCashiers = (int) $cashierUsers->count();
        $checkedTodayCount = (int) $todayRows->where('is_checked', true)->count();
        $pendingTodayCount = max($totalCashiers - $checkedTodayCount, 0);

        return view('admin.attendance.index', [
            'search' => $search,
            'date' => $date,
            'todayRows' => $todayRows,
            'attendanceRows' => $attendanceRows,
            'totalCashiers' => $totalCashiers,
            'checkedTodayCount' => $checkedTodayCount,
            'pendingTodayCount' => $pendingTodayCount,
        ]);
    }

    private function displayName(User $user): string
    {
        $fullName = trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''));

        if ($fullName !== '') {
            return $fullName;
        }

        $fallback = trim((string) ($user->name ?? ''));

        if ($fallback !== '') {
            return $fallback;
        }

        return trim((string) ($user->email ?? ('Cashier #' . $user->id)));
    }
}
