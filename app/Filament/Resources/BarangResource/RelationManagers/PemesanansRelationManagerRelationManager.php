<?php

namespace App\Filament\Resources\BarangResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PemesanansRelationManagerRelationManager extends RelationManager
{
    protected static string $relationship = 'pemesanans';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tgl_pesanan')
                    ->label('Tanggal Pesanan')
                    ->required()
                    ->default(now()),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'processed' => 'Diproses',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tgl_pesanan')
            ->columns([
                Tables\Columns\TextColumn::make('tgl_pesanan')
                    ->label('Tanggal Pesanan')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueColor(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'processed' => 'info',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->trueIcon(fn (string $state): string => match ($state) {
                        'approved' => 'heroicon-o-check-circle',
                        'processed' => 'heroicon-o-arrow-path',
                        'rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->falseIcon(fn (string $state): string => match ($state) {
                        'approved' => 'heroicon-o-check-circle',
                        'processed' => 'heroicon-o-arrow-path',
                        'rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->extraCellAttributes(['class' => 'w-12'])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'processed' => 'Diproses',
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Tanggal')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
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
