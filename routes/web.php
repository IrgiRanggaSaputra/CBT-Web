<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ReportPrintController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', function () {
    return view('admin');
});

Route::get('/peserta', function () {
    return view('peserta');
});

Route::get('/admin/reports/{test}/print', [ReportPrintController::class, 'printReport']);
