<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiPenjualan extends Model
{
    use HasFactory;
    
    protected $fillable = ['barang_id', 'jumlah', 'harga_jual', 'total', 'tanggal'];
    
    protected $casts = [
        'tanggal' => 'date',
    ];
    
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }
    
    protected static function boot()
    {
        parent::boot();
        
        // Update stok barang setelah transaksi penjualan dibuat
        static::created(function ($transaksi) {
            $barang = $transaksi->barang;
            $barang->decrement('stok', $transaksi->jumlah);
            
            // Cek jika stok mencapai level minimum
            if ($barang->stok <= $barang->level_minimum) {
                // Dispatch event untuk notifikasi stok kritis
                event(new \App\Events\StockReachedMinimum($barang));
            }
        });
        
        // Kembalikan stok jika transaksi dihapus
        static::deleted(function ($transaksi) {
            $transaksi->barang->increment('stok', $transaksi->jumlah);
        });
    }
}
