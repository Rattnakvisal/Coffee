<?php

namespace App\Providers;

use App\Services\AdminHeaderService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('admin.partials.header', function ($view): void {
            $request = request();

            if (! $request->routeIs('admin.*')) {
                return;
            }

            /** @var AdminHeaderService $adminHeader */
            $adminHeader = app(AdminHeaderService::class);

            $view->with([
                'adminHeaderSearchQuery' => trim((string) $request->query('q', '')),
                'adminHeaderSearchSuggestions' => $adminHeader->buildSearchSuggestions(),
                'adminHeaderNotifications' => $adminHeader->serializedNotificationsForPanel($request),
                'adminHeaderNotificationCount' => $adminHeader->unreadNotificationsCount($request),
            ]);
        });
    }
}
