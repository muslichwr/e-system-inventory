<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PemesananResource\Pages;
use App\Filament\Resources\PemesananResource\RelationManagers;
use App\Models\Pemesanan;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PemesananResource extends Resource
{
    protected static ?string $model = Pemesanan::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Inventaris';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Pesanan')
                    ->description('Isi informasi pesanan ulang barang')
                    ->schema([
                        Select::make('barang_id')
                            ->label('Barang')
                            ->relationship('barang', 'nama_barang', fn(Builder $query) => $query->whereColumn('stok', '<=', 'level_minimum'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $barang = \App\Models\Barang::find($state);
                                if ($barang) {
                                    $set('supplier_id', $barang->supplier_id);
                                }
                            }),

                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'nama_supplier')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Supplier akan otomatis terisi berdasarkan barang yang dipilih'),

                        DatePicker::make('tgl_pesanan')
                            ->label('Tanggal Pesanan')
                            ->required()
                            ->default(now()),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'processed' => 'Diproses',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Pemesanan $record): string => $record->barang->supplier->nama_supplier ?? ''),

                TextColumn::make('tgl_pesanan')
                    ->label('Tanggal Pesanan')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'processed' => 'info',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'approved' => 'heroicon-o-check-circle',
                        'processed' => 'heroicon-o-arrow-path',
                        'rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'processed' => 'Diproses',
                        default => ucfirst($state),
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat Tanggal')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tgl_pesanan', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'processed' => 'Diproses',
                    ])
                    ->searchable(),
                
                SelectFilter::make('barang.supplier_id')
                    ->relationship('barang.supplier', 'nama_supplier')
                    ->label('Supplier'),
                
                Tables\Filters\Filter::make('pending_approval')
                    ->label('Perlu Persetujuan')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'pending')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (Pemesanan $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Pemesanan $record) {
                        $record->update(['status' => 'approved']);
                    }),
                
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (Pemesanan $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Pemesanan $record) {
                        $record->update(['status' => 'rejected']);
                    }),
                
                Tables\Actions\Action::make('process')
                    ->label('Proses')
                    ->color('info')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (Pemesanan $record): bool => $record->status === 'approved')
                    ->requiresConfirmation()
                    ->action(function (Pemesanan $record) {
                        // Update stok barang setelah pesanan diproses
                        $barang = $record->barang;
                        $barang->update(['stok' => $barang->stok + 10]); // Contoh tambah stok 10
                        
                        $record->update(['status' => 'processed']);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

        public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->size(TextEntry\TextEntrySize::Large)
                    ->weight('bold'),
                
                TextEntry::make('barang.supplier.nama_supplier')
                    ->label('Supplier')
                    ->badge()
                    ->color('primary'),
                
                TextEntry::make('tgl_pesanan')
                    ->label('Tanggal Pesanan')
                    ->date('d F Y'),
                
                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'processed' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending - Perlu Persetujuan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'processed' => 'Telah Diproses',
                    }),
                
                TextEntry::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d F Y H:i'),
                
                TextEntry::make('updated_at')
                    ->label('Terakhir Diupdate')
                    ->dateTime('d F Y H:i'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPemesanans::route('/'),
            'create' => Pages\CreatePemesanan::route('/create'),
            'view' => Pages\ViewPemesanan::route('/{record}'),
            'edit' => Pages\EditPemesanan::route('/{record}/edit'),
        ];
    }
}
