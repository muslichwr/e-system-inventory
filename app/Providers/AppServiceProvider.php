<?php

namespace App\Providers;

use App\Models\Barang;
use App\Models\Laporan;
use App\Models\Pemesanan;
use App\Models\Supplier;
use App\Models\TransaksiPenjualan;
use App\Observers\BarangObserver;
use App\Observers\TransaksiPenjualanObserver;
use App\Policies\BarangPolicy;
use App\Policies\LaporanPolicy;
use App\Policies\PemesananPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\SupplierPolicy;
use App\Policies\TransaksiPenjualanPolicy;
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
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Barang::class, BarangPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(Pemesanan::class, PemesananPolicy::class);
        Gate::policy(Laporan::class, LaporanPolicy::class);
        Gate::policy(TransaksiPenjualan::class, TransaksiPenjualanPolicy::class);

    }
}
