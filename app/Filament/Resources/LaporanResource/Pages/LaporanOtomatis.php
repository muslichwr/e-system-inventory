<?php

namespace App\Filament\Resources\LaporanResource\Pages;

use App\Filament\Resources\LaporanResource;
use Filament\Resources\Pages\Page;

class LaporanOtomatis extends Page
{
    protected static string $resource = LaporanResource::class;

    protected static string $view = 'filament.resources.laporan-resource.pages.laporan-otomatis';
}
