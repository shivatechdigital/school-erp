<?php

use App\Http\Controllers\ReportCardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Report Card PDF
Route::get('/report-card/{student}/{exam}', [ReportCardController::class, 'generate'])
    ->name('report.card')
    ->middleware('auth');
