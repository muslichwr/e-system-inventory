<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use App\Models\Pemesanan;
use App\Models\TransaksiPenjualan;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Hitung total barang
        $totalBarang = Barang::count();
        
        // Hitung total stok semua barang
        $totalStok = Barang::sum('stok');
        
        // Hitung jumlah barang dengan stok kritis (≤ level_minimum)
        $barangKritis = Barang::whereColumn('stok', '<=', 'level_minimum')->count();
        
        // Hitung total penjualan hari ini
        $penjualanHariIni = TransaksiPenjualan::whereDate('tanggal', Carbon::today())
            ->sum('total');
        
        // Hitung pesanan yang perlu disetujui
        $pendingOrders = Pemesanan::where('status', 'pending')->count();

        return [
            Stat::make('Total Barang', $totalBarang)
                ->description('Jumlah jenis barang')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
            
            Stat::make('Total Stok', $totalStok)
                ->description('Unit keseluruhan')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('success'),
            
            Stat::make('Stok Kritis', $barangKritis)
                ->description('Perlu restock segera')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($barangKritis > 0 ? 'danger' : 'success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'wire:click' => '$emitUp("filterStatsWith", "low-stock")',
                ]),
            
            Stat::make('Penjualan Hari Ini', 'Rp ' . number_format($penjualanHariIni, 0, ',', '.'))
                ->description('Total penjualan')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            
            Stat::make('Perlu Persetujuan', $pendingOrders)
                ->description('Pesanan pending')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'wire:click' => '$emitUp("filterStatsWith", "pending-orders")',
                ]),
        ];
    }
}
