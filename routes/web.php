<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnergyController;

// 1. Route mặc định trang chủ dẫn thẳng về Dashboard tổng quan
Route::get('/', [EnergyController::class, 'dashboard'])->name('dashboard');

// 2. Các route dẫn đến 4 trang chức năng chính của hệ thống
Route::get('/financial', [EnergyController::class, 'financial'])->name('financial');
Route::get('/analytics', [EnergyController::class, 'analytics'])->name('analytics');
Route::get('/safety', [EnergyController::class, 'safety'])->name('safety');
Route::get('/simulator', [EnergyController::class, 'simulator'])->name('simulator');

// 3. Các route POST xử lý thay đổi trạng thái Rơ-le, Ngân sách và Ngưỡng quá tải Pmax
Route::post('/toggle-relay', [EnergyController::class, 'toggleRelay'])->name('toggle.relay');
Route::post('/save-budget', [EnergyController::class, 'saveBudget'])->name('save.budget');
Route::post('/save-pmax', [EnergyController::class, 'savePmax'])->name('save.pmax');
Route::post('/update-simulator', [EnergyController::class, 'updateSimulator'])->name('update.simulator');

// 4. Route kích hoạt kịch bản sự cố và Nhập/Xuất dữ liệu file JSON & CSV
Route::get('/scenario/{type}', [EnergyController::class, 'setScenario'])->name('scenario');
Route::post('/import-json', [EnergyController::class, 'importJson'])->name('import.json');
Route::get('/export-json', [EnergyController::class, 'exportJson'])->name('export.json');
Route::get('/export-csv', [EnergyController::class, 'exportCsv'])->name('export.csv');