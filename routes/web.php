<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitesController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SiteSettingsController;
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
    
    // Site Settings Routes
    Route::get('/site-settings', [SiteSettingsController::class, 'index'])->name('site.settings');
    
    // Category Routes
    Route::get('/site-settings/categories', [SiteSettingsController::class, 'getCategories'])->name('categories.get');
    Route::post('/site-settings/categories', [SiteSettingsController::class, 'storeCategory'])->name('categories.store');
    Route::get('/site-settings/categories/{id}', [SiteSettingsController::class, 'getCategory'])->name('categories.edit');
    Route::put('/site-settings/categories/{id}', [SiteSettingsController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/site-settings/categories/{id}', [SiteSettingsController::class, 'deleteCategory'])->name('categories.delete');
    
    // Country Routes
    Route::get('/site-settings/countries', [SiteSettingsController::class, 'getCountries'])->name('countries.get');
    Route::post('/site-settings/countries', [SiteSettingsController::class, 'storeCountry'])->name('countries.store');
    Route::get('/site-settings/countries/{id}', [SiteSettingsController::class, 'getCountry'])->name('countries.edit');
    Route::put('/site-settings/countries/{id}', [SiteSettingsController::class, 'updateCountry'])->name('countries.update');
    Route::delete('/site-settings/countries/{id}', [SiteSettingsController::class, 'deleteCountry'])->name('countries.delete');
    
    // Work Purpose Routes
    Route::get('/site-settings/purposes', [SiteSettingsController::class, 'getPurposes'])->name('purposes.get');
    Route::post('/site-settings/purposes', [SiteSettingsController::class, 'storePurpose'])->name('purposes.store');
    Route::get('/site-settings/purposes/{id}', [SiteSettingsController::class, 'getPurpose'])->name('purposes.edit');
    Route::put('/site-settings/purposes/{id}', [SiteSettingsController::class, 'updatePurpose'])->name('purposes.update');
    Route::delete('/site-settings/purposes/{id}', [SiteSettingsController::class, 'deletePurpose'])->name('purposes.delete');
    
    // Site Features Routes
    Route::get('/site-settings/features', [SiteSettingsController::class, 'getFeatures'])->name('features.get');
    Route::post('/site-settings/features', [SiteSettingsController::class, 'storeFeature'])->name('features.store');
    Route::get('/site-settings/features/{id}', [SiteSettingsController::class, 'getFeature'])->name('features.edit');
    Route::put('/site-settings/features/{id}', [SiteSettingsController::class, 'updateFeature'])->name('features.update');
    Route::delete('/site-settings/features/{id}', [SiteSettingsController::class, 'deleteFeature'])->name('features.delete');
    
    // Site Rating Routes
    Route::post('/site-settings/rating/{siteId}', [SiteSettingsController::class, 'updateSiteRating'])->name('site.rating.update');
    
    // Get all settings data for site creation
    Route::get('/site-settings/data', [SiteSettingsController::class, 'getSettingsData'])->name('site.settings.data');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/sessions/active', [SessionController::class, 'getActiveSessions'])->name('sessions.active');
    Route::post('/sessions/terminate/{sessionId}', [SessionController::class, 'terminate'])
         ->name('sessions.terminate')
         ->middleware('admin');
});

Route::get('sites/compatible-options', [App\Http\Controllers\SitesController::class, 'getCompatibleOptions'])->name('sites.compatible-options');

require __DIR__.'/auth.php';
