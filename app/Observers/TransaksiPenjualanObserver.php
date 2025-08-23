<?php

namespace App\Observers;

use App\Models\TransaksiPenjualan;
use Filament\Notifications\Notification;

class TransaksiPenjualanObserver
{
    public function created(TransaksiPenjualan $transaksiPenjualan)
    {
        // Update stok barang
        $barang = $transaksiPenjualan->barang;
        $stokBaru = $barang->stok - $transaksiPenjualan->jumlah;
        
        $barang->update(['stok' => $stokBaru]);
        
        // Kirim notifikasi jika stok mendekati level minimum
        if ($stokBaru <= $barang->level_minimum) {
            $message = "Stok {$barang->nama_barang} kritis! ({$stokBaru} unit)";
            
            // Kirim notifikasi ke semua pengguna dengan role yang sesuai
            \App\Models\User::all()->each(function ($user) use ($message, $barang) {
                Notification::make()
                    ->title('Stok Kritis')
                    ->warning()
                    ->body($message)
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('lihat')
                            ->url(route('filament.admin.resources.barangs.edit', $barang))
                    ])
                    ->sendToDatabase($user);
            });
        }
    }

    public function deleted(TransaksiPenjualan $transaksiPenjualan)
    {
        // Kembalikan stok jika transaksi dihapus
        $barang = $transaksiPenjualan->barang;
        $stokBaru = $barang->stok + $transaksiPenjualan->jumlah;
        
        $barang->update(['stok' => $stokBaru]);
    }
}
