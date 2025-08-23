<?php

namespace App\Observers;

use App\Models\Laporan;
use App\Models\Pemesanan;
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
        
        // Buat laporan penjualan
        Laporan::create([
            'barang_id' => $barang->id,
            'jenis_laporan' => 'penjualan',
            'tanggal' => $transaksiPenjualan->tanggal,
            'isi_laporan' => "Terjual {$transaksiPenjualan->jumlah} unit. Total penjualan: Rp " . number_format($transaksiPenjualan->total, 0, ',', '.'),
        ]);
        
        // Cek apakah stok mencapai level minimum
        if ($stokBaru <= $barang->level_minimum) {
            // Buat pesanan ulang otomatis dalam status pending
            Pemesanan::create([
                'barang_id' => $barang->id,
                'supplier_id' => $barang->supplier_id,
                'tgl_pesanan' => now(),
                'status' => 'pending'
            ]);
            
            // Kirim notifikasi ke semua pengguna
            $message = "Pesanan ulang untuk {$barang->nama_barang} telah dibuat (stok kritis: {$stokBaru} unit)";
            
            \App\Models\User::all()->each(function ($user) use ($message) {
                Notification::make()
                    ->title('Pesanan Ulang Dibuat')
                    ->warning()
                    ->body($message)
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('lihat')
                            ->url(route('filament.admin.resources.pemesanans.index'))
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
        
        // Hapus laporan penjualan terkait
        Laporan::where('barang_id', $barang->id)
            ->where('jenis_laporan', 'penjualan')
            ->where('tanggal', $transaksiPenjualan->tanggal)
            ->where('isi_laporan', 'like', "Terjual {$transaksiPenjualan->jumlah} unit. Total penjualan: %")
            ->delete();
    }
}