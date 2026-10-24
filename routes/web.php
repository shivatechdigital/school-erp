<?php

use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\StudentIdCardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Report Card PDF
Route::get('/report-card/{student}/{exam}', [ReportCardController::class, 'generate'])
    ->name('report.card')
    ->middleware('auth');

Route::get('/admin-section/id-cards/{studentIdCard}/download', [StudentIdCardController::class, 'download'])
    ->name('admin-section.id-cards.download')
    ->middleware('auth');
