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
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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
                            ->relationship('barang', 'nama_barang', fn (Builder $query) => $query->whereColumn('stok', '<=', 'level_minimum'))
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
                    ->description(fn (Pemesanan $record): string => $record->barang->supplier->nama_supplier ?? ''),
                
                TextColumn::make('tgl_pesanan')
                    ->label('Tanggal Pesanan')
                    ->date('d M Y')
                    ->sortable(),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'processed' => 'info',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'approved' => 'heroicon-o-check-circle',
                        'processed' => 'heroicon-o-arrow-path',
                        'rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
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
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'edit' => Pages\EditPemesanan::route('/{record}/edit'),
        ];
    }
}
