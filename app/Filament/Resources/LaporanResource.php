<?php

namespace App\Filament\Resources;

use App\Filament\Exports\LaporanExporter;
use App\Filament\Resources\LaporanResource\Pages;
use App\Filament\Resources\LaporanResource\RelationManagers;
use App\Models\Laporan;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Filament\Tables\Actions\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction as ExcelExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class LaporanResource extends Resource
{
    protected static ?string $model = Laporan::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan & Analisis';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Laporan')
                    ->description('Isi informasi laporan inventaris')
                    ->schema([
                        Select::make('barang_id')
                            ->label('Barang')
                            ->relationship('barang', 'nama_barang')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('jenis_laporan')
                            ->label('Jenis Laporan')
                            ->options([
                                'stok' => 'Laporan Stok',
                                'penjualan' => 'Laporan Penjualan',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('isi_laporan', null)),

                        DatePicker::make('tanggal')
                            ->label('Tanggal Laporan')
                            ->required()
                            ->default(now()),

                        Textarea::make('isi_laporan')
                            ->label('Isi Laporan')
                            ->required()
                            ->rows(8)
                            ->placeholder(fn(Forms\Get $get): string => match ($get('jenis_laporan')) {
                                'stok' => 'Contoh: Stok saat ini: 15 unit. Stok kritis: 5 unit. Perkiraan habis dalam 3 hari.',
                                'penjualan' => 'Contoh: Terjual 5 unit hari ini. Total penjualan minggu ini: 20 unit. Peningkatan penjualan 15% dari minggu lalu.',
                                default => 'Silakan masukkan isi laporan sesuai jenis yang dipilih.'
                            }),
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
                    ->description(fn(Laporan $record): string => $record->barang->supplier->nama_supplier ?? ''),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('jenis_laporan')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'stok' ? 'info' : 'success')
                    ->formatStateUsing(fn(string $state): string => $state === 'stok' ? 'Stok' : 'Penjualan')
                    ->sortable(),

                TextColumn::make('isi_laporan')
                    ->label('Ringkasan')
                    ->limit(60)
                    ->searchable()
                    ->extraCellAttributes(['class' => 'max-w-xs']),

                TextColumn::make('created_at')
                    ->label('Dibuat Tanggal')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                SelectFilter::make('jenis_laporan')
                    ->options([
                        'stok' => 'Stok',
                        'penjualan' => 'Penjualan',
                    ])
                    ->label('Jenis Laporan'),

                SelectFilter::make('barang.supplier_id')
                    ->relationship('barang.supplier', 'nama_supplier')
                    ->label('Supplier'),

                DateRangeFilter::make('tanggal')->alwaysShowCalendar()
                    ->label('Rentang Tanggal'),

                Filter::make('stok_kritis')
                    ->label('Laporan Stok Kritis')
                    ->query(fn(Builder $query): Builder => $query
                        ->where('jenis_laporan', 'stok')
                        ->where('isi_laporan', 'like', '%Stok kritis%')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
        ->headerActions([
            Tables\Actions\Action::make('generatePeriodicReport')
                ->label('Buat Laporan Periodik')
                ->color('primary')
                ->icon('heroicon-o-document-text')
                ->form([
                    Forms\Components\Select::make('report_type')
                        ->label('Jenis Laporan')
                        ->options([
                            'daily' => 'Harian',
                            'weekly' => 'Mingguan',
                            'monthly' => 'Bulanan',
                        ])
                        ->required(),
                    
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->required()
                        ->default(now()->subDays(7)),
                    
                    Forms\Components\DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->required()
                        ->default(now()),
                ])
                ->action(function (array $data) {
                    // Hitung total penjualan dalam periode
                    $totalPenjualan = \App\Models\TransaksiPenjualan::whereBetween('tanggal', [$data['start_date'], $data['end_date']])
                        ->sum('total');
                    
                    // Hitung jumlah transaksi
                    $jumlahTransaksi = \App\Models\TransaksiPenjualan::whereBetween('tanggal', [$data['start_date'], $data['end_date']])
                        ->count();
                    
                    // Hitung rata-rata transaksi
                    $rataRata = $jumlahTransaksi > 0 ? $totalPenjualan / $jumlahTransaksi : 0;
                    
                    // Hitung produk terlaris
                    $produkTerlaris = \App\Models\TransaksiPenjualan::select('barang_id', \Illuminate\Support\Facades\DB::raw('SUM(jumlah) as total_terjual'))
                        ->whereBetween('tanggal', [$data['start_date'], $data['end_date']])
                        ->groupBy('barang_id')
                        ->orderBy('total_terjual', 'desc')
                        ->with('barang')
                        ->first();
                    
                    $namaProdukTerlaris = $produkTerlaris ? $produkTerlaris->barang->nama_barang . " ({$produkTerlaris->total_terjual} unit)" : 'Tidak ada';
                    
                    // Buat isi laporan
                    $isiLaporan = "LAPORAN PENJUALAN PERIODE\n";
                    $isiLaporan .= "Periode: {$data['start_date']} sampai {$data['end_date']}\n";
                    $isiLaporan .= "Jenis: " . ucfirst($data['report_type']) . "\n\n";
                    $isiLaporan .= "RINGKASAN KEUANGAN:\n";
                    $isiLaporan .= "- Total Penjualan: Rp " . number_format($totalPenjualan, 0, ',', '.') . "\n";
                    $isiLaporan .= "- Jumlah Transaksi: {$jumlahTransaksi}\n";
                    $isiLaporan .= "- Rata-rata Transaksi: Rp " . number_format($rataRata, 0, ',', '.') . "\n\n";
                    $isiLaporan .= "PRODUK TERLARIS:\n";
                    $isiLaporan .= "- {$namaProdukTerlaris}\n";
                    
                    // Simpan ke database
                    \App\Models\Laporan::create([
                        'barang_id' => null, // Laporan periodik tidak terkait barang spesifik
                        'jenis_laporan' => 'penjualan',
                        'tanggal' => now(),
                        'isi_laporan' => $isiLaporan,
                    ]);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Laporan Berhasil Dibuat')
                        ->success()
                        ->body('Laporan periodik telah ditambahkan ke daftar laporan')
                        ->send();
                }),
            
            // Export action yang sudah ada
            ExportAction::make()->exporter(LaporanExporter::class)
        ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Laporan Baru'),
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
            'index' => Pages\ListLaporans::route('/'),
            'create' => Pages\CreateLaporan::route('/create'),
            'view' => Pages\ViewLaporan::route('/{record}'),
            'edit' => Pages\EditLaporan::route('/{record}/edit'),
        ];
    }
}
