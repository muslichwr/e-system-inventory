<?php

namespace App\Providers;

use App\Models\Barang;
use App\Models\TransaksiPenjualan;
use App\Observers\BarangObserver;
use App\Observers\TransaksiPenjualanObserver;
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
        TransaksiPenjualan::observe(TransaksiPenjualanObserver::class);
        Barang::observe(BarangObserver::class);
    }
}
