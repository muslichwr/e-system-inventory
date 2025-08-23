<?php

namespace App\Filament\Exports;

use App\Models\Laporan;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class LaporanExporter extends Exporter
{
    protected static ?string $model = Laporan::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            
            ExportColumn::make('barang.nama_barang')
                ->label('Nama Barang'),
            
            ExportColumn::make('jenis_laporan')
                ->label('Jenis Laporan')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'stok' => 'Stok',
                    'penjualan' => 'Penjualan',
                    default => ucfirst($state),
                }),
            
            ExportColumn::make('tanggal')
                ->label('Tanggal')
                ->formatStateUsing(fn ($state): string => $state ? \Carbon\Carbon::parse($state)->format('d-m-Y') : '-'),
            
            ExportColumn::make('isi_laporan')
                ->label('Isi Laporan'),
            
            ExportColumn::make('created_at')
                ->label('Dibuat Pada')
                ->formatStateUsing(fn ($state): string => $state ? \Carbon\Carbon::parse($state)->format('d-m-Y H:i:s') : '-'),
            
            ExportColumn::make('updated_at')
                ->label('Diupdate Pada')
                ->formatStateUsing(fn ($state): string => $state ? \Carbon\Carbon::parse($state)->format('d-m-Y H:i:s') : '-'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Laporan export telah selesai dan ' . number_format($export->successful_rows) . ' ' . str('baris')->plural($export->successful_rows) . ' berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('baris')->plural($failedRowsCount) . ' gagal diekspor.';
        }

        return $body;
    }
}