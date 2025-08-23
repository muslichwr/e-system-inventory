<?php

namespace App\Observers;

use App\Models\Laporan;
use App\Models\Pemesanan;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class PemesananObserver
{
    /**
     * Handle the Pemesanan "created" event.
     */
    public function created(Pemesanan $pemesanan)
    {
        // Jika pesanan dibuat dalam status pending, kirim notifikasi langsung
        if ($pemesanan->status === 'pending') {
            $message = "Pesanan baru untuk {$pemesanan->barang->nama_barang} telah dibuat dan menunggu persetujuan";
            
            // Kirim notifikasi ke manajer/admin
            \App\Models\User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['Manajer', 'Admin']);
            })->each(function ($user) use ($message, $pemesanan) {
                Notification::make()
                    ->title('Pesanan Baru Menunggu Persetujuan')
                    ->info()
                    ->body($message)
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('lihat')
                            ->url(route('filament.admin.resources.pemesanans.edit', $pemesanan))
                    ])
                    ->sendToDatabase($user);
            });
        }
    }

    /**
     * Handle the Pemesanan "updated" event.
     */
    public function updated(Pemesanan $pemesanan)
    {
        // Cek jika status berubah menjadi 'processed'
        if ($pemesanan->isDirty('status') && $pemesanan->status === 'processed') {
            // Ambil barang terkait
            $barang = $pemesanan->barang;
            
            // Hitung target stok (level_minimum + 10)
            $targetStok = $barang->level_minimum + 10;
            
            // Update stok barang
            $barang->update(['stok' => $targetStok]);
            
            // Perbaikan: Konversi string ke Carbon object sebelum format
            $tanggalPesanan = $pemesanan->tgl_pesanan;
            
            // Jika $tanggalPesanan adalah string, konversi ke Carbon
            if (is_string($tanggalPesanan)) {
                $tanggalFormatted = Carbon::parse($tanggalPesanan)->format('d-m-Y');
            } else {
                // Jika sudah Carbon object
                $tanggalFormatted = $tanggalPesanan->format('d-m-Y');
            }
            
            // Buat laporan penambahan stok
            Laporan::create([
                'barang_id' => $barang->id,
                'jenis_laporan' => 'stok',
                'tanggal' => $pemesanan->tgl_pesanan,
                'isi_laporan' => "Stok ditambahkan hingga {$targetStok} unit (dari pesanan tanggal {$tanggalFormatted})",
            ]);
            
            // Kirim notifikasi ke semua pengguna
            $message = "Stok {$barang->nama_barang} telah ditambahkan hingga {$targetStok} unit";
            
            \App\Models\User::all()->each(function ($user) use ($message, $barang) {
                Notification::make()
                    ->title('Stok Telah Diupdate')
                    ->success()
                    ->body($message)
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('lihat')
                            ->url(route('filament.admin.resources.barangs.edit', $barang))
                    ])
                    ->sendToDatabase($user);
            });
        }
        
        // Cek jika status berubah menjadi 'approved'
        if ($pemesanan->isDirty('status') && $pemesanan->status === 'approved') {
            $message = "Pesanan untuk {$pemesanan->barang->nama_barang} telah disetujui dan siap diproses";
            
            // Kirim notifikasi ke pegawai gudang
            \App\Models\User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['Pegawai Gudang', 'Admin']);
            })->each(function ($user) use ($message, $pemesanan) {
                Notification::make()
                    ->title('Pesanan Disetujui')
                    ->success()
                    ->body($message)
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('lihat')
                            ->url(route('filament.admin.resources.pemesanans.edit', $pemesanan))
                    ])
                    ->sendToDatabase($user);
            });
        }
    }
}