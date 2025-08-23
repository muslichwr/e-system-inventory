<?php

namespace App\Filament\Resources\LaporanResource\Pages;

use App\Filament\Resources\LaporanResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewLaporan extends ViewRecord
{
    protected static string $resource = LaporanResource::class;

        public function infolist(Infolist $infolist): infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Laporan')
                    ->schema([
                        Infolists\Components\TextEntry::make('barang.nama_barang')
                            ->label('Nama Barang')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                        
                        Infolists\Components\TextEntry::make('barang.supplier.nama_supplier')
                            ->label('Supplier')
                            ->badge()
                            ->color('primary'),
                        
                        Infolists\Components\TextEntry::make('jenis_laporan')
                            ->label('Jenis Laporan')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'stok' ? 'info' : 'success')
                            ->formatStateUsing(fn (string $state): string => $state === 'stok' ? 'Stok' : 'Penjualan'),
                        
                        Infolists\Components\TextEntry::make('tanggal')
                            ->label('Tanggal')
                            ->date('d F Y'),
                    ])
                    ->columns(2),
                
                Infolists\Components\Section::make('Isi Laporan')
                    ->schema([
                        Infolists\Components\TextEntry::make('isi_laporan')
                            ->label('Detail')
                            ->prose()
                            ->extraAttributes(['class' => 'whitespace-pre-line']),
                    ]),
                
                Infolists\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d F Y H:i'),
                        
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Terakhir Diupdate')
                            ->dateTime('d F Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}
