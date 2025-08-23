<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiPenjualan extends Model
{
    protected $fillable = ['barang_id', 'jumlah', 'harga_jual', 'total', 'tanggal'];

    protected $casts = [
        'harga_jual' => 'decimal:2',
        'total' => 'decimal:2',
        'tanggal' => 'date',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }
}
