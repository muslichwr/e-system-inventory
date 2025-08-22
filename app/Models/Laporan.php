<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Laporan extends Model
{
    protected $fillable = ['barang_id', 'jenis_laporan', 'tanggal', 'isi_laporan'];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }
}
