<?php

namespace App\Filament\Widgets;

use App\Models\TransaksiPenjualan;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Penjualan';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => true,
            ],
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'ticks' => [
                    'callback' => 'function(value) { return "Rp " + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); }',
                ],
            ],
        ],
    ];

    protected function getData(): array
    {
        // Ambil data penjualan 7 hari terakhir
        $data = Trend::model(TransaksiPenjualan::class)
            ->between(
                start: now()->subDays(6),
                end: now()
            )
            ->perDay()
            ->sum('total');
        
        return [
            'datasets' => [
                [
                    'label' => 'Penjualan Harian',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#3B82F6',
                    'borderColor' => '#2563EB',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => 
                Carbon::parse($value->date)->format('d M')
            ),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}