<?php

use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');

Route::get('/laporan/pdf', [LaporanController::class, 'exportPdf'])
            ->name('laporan.pdf');
});
