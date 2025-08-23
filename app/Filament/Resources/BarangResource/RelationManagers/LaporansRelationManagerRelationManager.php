<?php

namespace App\Filament\Resources\BarangResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LaporansRelationManagerRelationManager extends RelationManager
{
    protected static string $relationship = 'laporans';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal Laporan')
                    ->required()
                    ->default(now()),
                
                Forms\Components\Select::make('jenis_laporan')
                    ->label('Jenis Laporan')
                    ->options([
                        'stok' => 'Stok',
                        'penjualan' => 'Penjualan',
                    ])
                    ->required(),
                
                Forms\Components\Textarea::make('isi_laporan')
                    ->label('Isi Laporan')
                    ->required()
                    ->rows(5),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tanggal')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('jenis_laporan')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'stok' ? 'info' : 'success')
                    ->formatStateUsing(fn (string $state): string => $state === 'stok' ? 'Stok' : 'Penjualan'),
                
                Tables\Columns\TextColumn::make('isi_laporan')
                    ->label('Ringkasan')
                    ->limit(50)
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Tanggal')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_laporan')
                    ->options([
                        'stok' => 'Stok',
                        'penjualan' => 'Penjualan',
                    ])
                    ->searchable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['barang_id'] = $this->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
