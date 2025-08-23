<?php

namespace App\Filament\Widgets;

use App\Models\TransaksiPenjualan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TopProducts extends BaseWidget
{
    protected static ?int $sort = 5;
    
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Ambil 3 produk terlaris berdasarkan jumlah penjualan
        $topProducts = TransaksiPenjualan::select('barang_id', DB::raw('SUM(jumlah) as total_terjual'))
            ->groupBy('barang_id')
            ->orderBy('total_terjual', 'desc')
            ->with('barang')
            ->limit(3)
            ->get();
            
        $stats = [];
        
        foreach ($topProducts as $index => $product) {
            $stats[] = Stat::make($product->barang->nama_barang, "{$product->total_terjual} unit")
                ->description('Produk Terlaris #' . ($index + 1))
                ->descriptionIcon('heroicon-m-fire')
                ->color('success');
        }
        
        // Jika tidak ada produk terlaris, tampilkan pesan
        if (empty($stats)) {
            $stats[] = Stat::make('Analisis Produk', 'Belum ada data penjualan')
                ->description('Data penjualan akan muncul setelah ada transaksi')
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('gray');
        }
        
        return $stats;
    }
}
