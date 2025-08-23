<?php

namespace App\Providers;

use App\Models\Barang;
use App\Models\Laporan;
use App\Models\Pemesanan;
use App\Models\Supplier;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use App\Observers\BarangObserver;
use App\Observers\PemesananObserver;
use App\Observers\TransaksiPenjualanObserver;
use App\Policies\BarangPolicy;
use App\Policies\LaporanPolicy;
use App\Policies\PemesananPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\SupplierPolicy;
use App\Policies\TransaksiPenjualanPolicy;
use App\Policies\UserPolicy;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        TransaksiPenjualan::observe(TransaksiPenjualanObserver::class);
        Barang::observe(BarangObserver::class);
        Pemesanan::observe(PemesananObserver::class);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            
            // Jadwalkan pengecekan pesanan pending setiap hari jam 9 pagi
            $schedule->command('reminder:pending-orders --check')
                     ->dailyAt('09:00')
                     ->withoutOverlapping();
        });

        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Barang::class, BarangPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(Pemesanan::class, PemesananPolicy::class);
        Gate::policy(Laporan::class, LaporanPolicy::class);
        Gate::policy(TransaksiPenjualan::class, TransaksiPenjualanPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
