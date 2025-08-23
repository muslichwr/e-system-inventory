<?php

namespace App\Observers;

use App\Models\Barang;
use Filament\Notifications\Notification;

class BarangObserver
{
    public function updated(Barang $barang)
    {
        // Cek jika stok sebelumnya kritis dan sekarang sudah normal
        if ($barang->isDirty('stok')) {
            $oldStok = $barang->getOriginal('stok');
            $newStok = $barang->stok;
            
            // Jika stok sebelumnya di bawah level minimum dan sekarang di atasnya
            if ($oldStok <= $barang->level_minimum && $newStok > $barang->level_minimum) {
                $message = "Stok {$barang->nama_barang} telah kembali normal! ({$newStok} unit)";
                
                // Kirim notifikasi ke semua pengguna
                \App\Models\User::all()->each(function ($user) use ($message, $barang) {
                    Notification::make()
                        ->title('Stok Telah Normal')
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
}