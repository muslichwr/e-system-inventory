<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Inventaris';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Supplier')
                    ->description('Masukkan data lengkap supplier')
                    ->schema([
                        Forms\Components\TextInput::make('nama_supplier')
                            ->label('Nama Supplier')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: PT. Elektronik Maju Jaya'),

                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->required()
                            ->rows(3)
                            ->placeholder('Masukkan alamat lengkap supplier'),

                        Forms\Components\TextInput::make('kontak')
                            ->label('Kontak')
                            ->required()
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('Contoh: 0812-3456-7890'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_supplier')
                    ->label('Nama Supplier')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('kontak')
                    ->label('Kontak')
                    ->searchable(),

                Tables\Columns\TextColumn::make('barangs_count')
                    ->label('Jumlah Produk')
                    ->counts('barangs')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Tanggal')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withCount('barangs'));
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
