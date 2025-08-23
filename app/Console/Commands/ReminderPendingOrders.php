<?php

namespace App\Console\Commands;

use App\Models\Pemesanan;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class ReminderPendingOrders extends Command
{
    protected $signature = 'reminder:pending-orders';
    
    protected $description = 'Mengirim pengingat untuk pesanan yang belum diproses lebih dari 2 hari';

    public function handle()
    {
        // Ambil pesanan pending yang lebih dari 2 hari
        $pendingOrders = Pemesanan::where('status', 'pending')
            ->where('tgl_pesanan', '<=', now()->subDays(2))
            ->with(['barang', 'supplier'])
            ->get();
            
        $count = 0;
        
        foreach ($pendingOrders as $order) {
            $message = "Pengingat: Pesanan untuk {$order->barang->nama_barang} dari supplier {$order->supplier->nama_supplier} masih pending sejak {$order->tgl_pesanan->format('d M Y')}";
            
            // Kirim notifikasi ke user dengan role Manajer
            \App\Models\User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['Manajer', 'Admin']);
            })->each(function ($user) use ($message, $order) {
                Notification::make()
                    ->title('Pengingat Pesanan Pending')
                    ->warning()
                    ->body($message)
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('lihat')
                            ->url(route('filament.admin.resources.pemesanans.edit', $order))
                    ])
                    ->sendToDatabase($user);
            });
            
            $count++;
        }
        
        $this->info("{$count} pengingat pesanan pending telah dikirim.");
        
        if ($count == 0) {
            $this->info("Tidak ada pesanan pending yang perlu diingatkan.");
        }
    }
}
