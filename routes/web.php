<?php

use App\Http\Controllers\RecordController;
use App\Models\Record;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard/{state?}', [RecordController::class,'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');
Route::resource('records', RecordController::class);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
