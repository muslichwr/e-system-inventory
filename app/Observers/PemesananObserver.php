<?php

namespace App\Observers;

use App\Models\Laporan;
use App\Models\Pemesanan;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class PemesananObserver
{
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
    }
}