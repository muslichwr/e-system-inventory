<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    protected static ?int $limit = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Barang::query()->whereColumn('stok', '<=', 'level_minimum')->orderBy('stok', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok')
                    ->sortable()
                    ->badge()
                    ->color('danger'),
                
                Tables\Columns\TextColumn::make('level_minimum')
                    ->label('Level Min.')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('kekurangan')
                    ->label('Perlu Ditambah')
                    ->getStateUsing(function (Barang $record): string {
                        $kekurangan = $record->level_minimum - $record->stok + 10;
                        return max(0, $kekurangan) . ' unit';
                    })
                    ->badge()
                    ->color('warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('restock')
                    ->label('Pesan Ulang')
                    ->color('primary')
                    ->icon('heroicon-o-arrow-path')
                    ->url(function (Barang $record) {
                        return route('filament.admin.resources.pemesanans.create', [
                            'barang_id' => $record->id,
                            'supplier_id' => $record->supplier_id
                        ]);
                    })
                    ->visible(fn (Barang $record): bool => true),
            ])
            ->heading('Daftar Barang dengan Stok Kritis')
            ->description('Barang yang stoknya mencapai atau di bawah level minimum');
    }
}
