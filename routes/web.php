<?php

use App\Http\Controllers\BranchTargetController;
use App\Models\District;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

//    dd(District::where('name', 'Muzaffarabad')->first());
    return view('welcome');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {
    Route::get('/dashboard', function () { return view('dashboard'); })->name('dashboard');
    Route::resource('branch-targets', BranchTargetController::class);
});
