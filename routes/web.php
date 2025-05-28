<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitesController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'check.status'])->name('dashboard');

Route::middleware(['auth', 'check.status'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');


    Route::get('/sites', [SitesController::class, 'sites'])->name('sites');
    Route::get('/sites/fetch', [SitesController::class, 'fetchSite'])->name('sites.fetch');
    Route::post('/sites', [SitesController::class, 'storeSite'])->name('sites.store');
    Route::get('/sites/edit/{id}', [SitesController::class, 'editSite'])->name('sites.edit');
    Route::put('/sites/{id}', [SitesController::class, 'updateSite'])->name('sites.update');
    Route::delete('/sites/{id}', [SitesController::class, 'destroySite'])->name('sites.destroy');


    Route::get('/sites/type', [SitesController::class, 'typeInex'])->name('type.index');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/sessions/active', [SessionController::class, 'getActiveSessions'])->name('sessions.active');
    Route::post('/sessions/terminate/{sessionId}', [SessionController::class, 'terminate'])
         ->name('sessions.terminate')
         ->middleware('admin');
});

require __DIR__.'/auth.php';
