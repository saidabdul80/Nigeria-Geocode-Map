<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectOutlookController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\UserController;
use App\Models\Lga;
use App\Models\Record;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard/{state?}', [RecordController::class,'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');
//Route::resource('records', RecordController::class);

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
    // Other admin-only routes
});

Route::middleware(['auth', 'verified','permission:view_records'])->group(function () {
    Route::resource('records', RecordController::class)->except(['create', 'store']);
    Route::get('records/create', [RecordController::class, 'create'])
        ->middleware('permission:create_records');
    Route::post('records', [RecordController::class, 'store'])->name('records.store');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
// routes/api.php
Route::get('/lgas/{lga}/wards', function (Lga $lga) {
    return $lga->wards;
});
// routes/web.php
Route::resource('project-outlooks', ProjectOutlookController::class)
    ->middleware(['auth', 'verified']);

// routes/api.php
Route::get('/wards', function (Request $request) {
    $query = Ward::query()
        ->select('id', 'name', 'lga_id')
        ->with(['lga:id,name']);  // Include LGA name for display

    if ($request->lga_ids) {
        $query->whereIn('lga_id', explode(',', $request->lga_ids));
    }

    if ($request->search) {
        $query->where('name', 'like', "%{$request->search}%");
    }

    return $query->orderBy('name')
                ->limit(200)  // Safe limit for dropdown
                ->get();
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
