<?php

namespace App\Listeners;

use App\Events\StockReachedMinimum;
use App\Models\Pemesanan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateRestockOrder
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(StockReachedMinimum $event): void
    {
        // Cek apakah sudah ada pesanan pending untuk barang ini
        $existingOrder = Pemesanan::where('barang_id', $event->barang->id)
            ->where('status', 'pending')
            ->exists();
            
        if (!$existingOrder) {
            // Buat pesanan ulang otomatis
            Pemesanan::create([
                'barang_id' => $event->barang->id,
                'supplier_id' => $event->barang->supplier_id,
                'tgl_pesanan' => now(),
                'status' => 'pending'
            ]);
            
            Log::info('Pesanan ulang otomatis dibuat untuk barang: ' . $event->barang->nama_barang);
        }
    }
}
