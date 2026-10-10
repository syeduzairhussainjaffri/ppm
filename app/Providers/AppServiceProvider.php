<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use App\Models\PpmRecord;
use App\Models\User;
use App\Models\Permission;

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
        // Core Static Gates
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('manage-users', fn (User $user) => $user->isAdmin() || $user->hasPermission('manage_users'));
        Gate::define('delete-records', fn (User $user) => $user->isAdmin() || $user->hasPermission('delete_ppm'));
        Gate::define('edit-records', fn (User $user) => $user->isAdmin() || $user->hasPermission('edit_ppm'));
        Gate::define('create-records', fn (User $user) => $user->isAdmin() || $user->hasPermission('create_ppm'));
        Gate::define('view-reports', fn (User $user) => $user->hasPermission('view_reports'));

        // Dynamic Database Gates for all permissions
        try {
            if (Schema::hasTable('permissions')) {
                foreach (Permission::all() as $permission) {
                    Gate::define($permission->slug, function (User $user) use ($permission) {
                        return $user->hasPermission($permission->slug);
                    });
                }
            }
        } catch (\Throwable $e) {
            // Fail-safe if migration is not run yet
        }

        // Global Layout View Composer
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
