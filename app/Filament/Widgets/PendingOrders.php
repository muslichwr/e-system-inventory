<?php

namespace App\Filament\Widgets;

use App\Models\Pemesanan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingOrders extends BaseWidget
{
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = 'full';

    protected static ?int $limit = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Pemesanan::query()
                    ->where('status', 'pending')
                    ->with(['barang', 'supplier'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('barang.supplier.nama_supplier')
                    ->label('Supplier')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('tgl_pesanan')
                    ->label('Tanggal Pesanan')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn (string $state): string => 'Pending - Perlu Persetujuan'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->url(function (Pemesanan $record) {
                        return route('filament.admin.resources.pemesanans.edit', $record);
                    }),
                
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->url(function (Pemesanan $record) {
                        return route('filament.admin.resources.pemesanans.edit', $record);
                    }),
            ])
            ->heading('Pesanan yang Perlu Disetujui')
            ->description('Daftar pesanan dengan status pending');
    }
}
