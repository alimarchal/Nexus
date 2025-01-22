<?php

use App\Http\Controllers\DailyPositionController;
use App\Http\Controllers\BranchTargetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use App\Models\District;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {

//    dd(District::where('name', 'Muzaffarabad')->first());
    return view('welcome');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    Route::resource('branch-targets', BranchTargetController::class);
    Route::resource('daily-positions', DailyPositionController::class);
    Route::get('daily-positions/{id}', [DailyPositionController::class, 'view'])->name('daily-positions.view');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');


});
