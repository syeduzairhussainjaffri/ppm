<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\PpmRecord;

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
        View::composer('ppm.layout', function ($view) {
            try {
                if (Schema::hasTable('ppm_records')) {
                    $today = now()->toDateString();

                    $dueToday = PpmRecord::whereDate('next_ppm_date', $today)
                        ->orderBy('id', 'desc')
                        ->get();

                    $overdue = PpmRecord::whereNotNull('next_ppm_date')
                        ->whereDate('next_ppm_date', '<', $today)
                        ->where(function ($q) {
                            $q->where('status', '!=', 'Completed')
                              ->orWhereNull('status');
                        })
                        ->orderBy('next_ppm_date', 'desc')
                        ->take(15)
                        ->get();

                    $upcoming = PpmRecord::whereDate('next_ppm_date', '>', $today)
                        ->whereDate('next_ppm_date', '<=', now()->addDays(7)->toDateString())
                        ->orderBy('next_ppm_date', 'asc')
                        ->take(15)
                        ->get();

                    $upcomingAll = PpmRecord::whereNotNull('next_ppm_date')
                        ->whereDate('next_ppm_date', '>=', $today)
                        ->orderBy('next_ppm_date', 'asc')
                        ->take(15)
                        ->get();

                    $view->with([
                        'navDueToday' => $dueToday,
                        'navOverdue' => $overdue,
                        'navUpcoming' => $upcoming,
                        'navUpcomingAll' => $upcomingAll,
                        'navNotificationCount' => $dueToday->count() + $overdue->count(),
                    ]);
                }
            } catch (\Throwable $e) {
                // Fail-safe if db is not ready
                $view->with([
                    'navDueToday' => collect(),
                    'navOverdue' => collect(),
                    'navUpcoming' => collect(),
                    'navNotificationCount' => 0,
                ]);
            }
        });
    }
}
