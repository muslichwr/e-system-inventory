<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\RelationManagers;
use App\Filament\Resources\BarangResource\RelationManagers\LaporansRelationManagerRelationManager;
use App\Filament\Resources\BarangResource\RelationManagers\PemesanansRelationManagerRelationManager;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Inventaris';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Barang')
                    ->description('Masukkan data lengkap barang elektronik')
                    ->schema([
                        TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Smartphone Samsung Galaxy S24'),

                        TextInput::make('harga')
                            ->label('Harga')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000)
                            ->minValue(0)
                            ->placeholder('0')
                            ->rules([
                                'regex:/^\d+(\.\d{1,2})?$/'
                            ]),

                        TextInput::make('stok')
                            ->label('Stok Awal')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('0'),

                        TextInput::make('level_minimum')
                            ->label('Level Minimum')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Sistem akan memberi notifikasi ketika stok mencapai level ini')
                            ->placeholder('Contoh: 5'),

                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'nama_supplier')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nama_supplier')
                                    ->label('Nama Supplier')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('alamat')
                                    ->label('Alamat')
                                    ->required()
                                    ->rows(3),
                                Forms\Components\TextInput::make('kontak')
                                    ->label('Kontak')
                                    ->required()
                                    ->tel()
                                    ->maxLength(20),
                            ])
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Barang $record): string => $record->supplier->nama_supplier ?? ''),

                TextColumn::make('harga')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stok')
                    ->label('Stok')
                    ->sortable()
                    ->badge()
                    ->color(
                        fn(string $state, Barang $record): string =>
                        $state <= $record->level_minimum ? 'danger' : ($state <= $record->level_minimum * 2 ? 'warning' : 'success')
                    ),

                TextColumn::make('level_minimum')
                    ->label('Level Min.')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('needs_restock')
                    ->label('Perlu Restock')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->extraCellAttributes(['class' => 'w-12'])
                    ->getStateUsing(fn(Barang $record): bool => $record->stok <= $record->level_minimum)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Tanggal')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'nama_supplier')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Rendah')
                    ->query(fn(Builder $query): Builder => $query->whereColumn('stok', '<=', 'level_minimum')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('restock')
                    ->label('Pesan Ulang')
                    ->color('primary')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn(Barang $record): bool => $record->stok <= $record->level_minimum)
                    ->action(function (Barang $record) {
                        // Akan diimplementasikan lebih lanjut
                        return redirect()->route('filament.admin.resources.pemesanans.create', [
                            'barang_id' => $record->id,
                            'supplier_id' => $record->supplier_id
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('nama_barang')
                    ->label('Nama Barang')
                    ->size(TextEntry\TextEntrySize::Large)
                    ->weight('bold'),

                TextEntry::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->badge()
                    ->color('primary'),

                TextEntry::make('harga')
                    ->label('Harga')
                    ->money('IDR')
                    ->size(TextEntry\TextEntrySize::Large),

                TextEntry::make('stok')
                    ->label('Stok Tersedia')
                    ->badge()
                    ->color(
                        fn(string $state, Barang $record): string =>
                        $state <= $record->level_minimum ? 'danger' : ($state <= $record->level_minimum * 2 ? 'warning' : 'success')
                    )
                    ->size(TextEntry\TextEntrySize::Large)
                    ->weight('bold'),

                TextEntry::make('level_minimum')
                    ->label('Level Minimum')
                    ->badge()
                    ->color('warning'),

                TextEntry::make('needs_restock')
                    ->label('Status Stok')
                    ->badge()
                    ->color(
                        fn(Barang $record): string =>
                        $record->stok <= $record->level_minimum ? 'danger' : 'success'
                    )
                    ->formatStateUsing(
                        fn(Barang $record): string =>
                        $record->stok <= $record->level_minimum ? 'STOK RENDAH - PERLU RESTOCK' : 'STOK AMAN'
                    ),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PemesanansRelationManagerRelationManager::class,
            LaporansRelationManagerRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'view' => Pages\ViewBarang::route('/{record}'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}
