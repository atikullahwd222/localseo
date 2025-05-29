<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitesController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SiteSettingsController;
use App\Http\Controllers\PermissionController;
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

    // Sites Routes
    Route::get('/sites', [SitesController::class, 'sites'])->name('sites');
    Route::get('/sites/fetch', [SitesController::class, 'fetchSite'])->name('sites.fetch');
    Route::get('/sites/compatible-options', [SitesController::class, 'getCompatibleOptions'])->name('sites.compatible-options');
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

// After the existing auth middleware group, add role-specific routes
Route::middleware(['auth', 'role:admin,editor', 'check.status'])->group(function () {
    // User management routes (admin, editor)
    Route::get('/admin/users/pending', [App\Http\Controllers\UserManagementController::class, 'pendingUsers'])
        ->name('admin.users.pending');
    Route::post('/admin/users/{id}/approve', [App\Http\Controllers\UserManagementController::class, 'approveUser'])
        ->name('admin.users.approve');
});

Route::middleware(['auth', 'role:admin', 'check.status'])->group(function () {
    // Admin-only routes
    Route::get('/admin/users', [App\Http\Controllers\UserManagementController::class, 'index'])
        ->name('admin.users.index');
    Route::post('/admin/users/{id}/role', [App\Http\Controllers\UserManagementController::class, 'changeRole'])
        ->name('admin.users.change-role');
    Route::post('/admin/users/{id}/status', [App\Http\Controllers\UserManagementController::class, 'changeStatus'])
        ->name('admin.users.change-status');
    
    // Role management
    Route::get('/admin/roles', [App\Http\Controllers\RoleController::class, 'index'])
        ->name('admin.roles.index');
    Route::get('/admin/roles/create', [App\Http\Controllers\RoleController::class, 'create'])
        ->name('admin.roles.create');
    Route::post('/admin/roles', [App\Http\Controllers\RoleController::class, 'store'])
        ->name('admin.roles.store');
    Route::get('/admin/roles/{id}/edit', [App\Http\Controllers\RoleController::class, 'edit'])
        ->name('admin.roles.edit');
    Route::put('/admin/roles/{id}', [App\Http\Controllers\RoleController::class, 'update'])
        ->name('admin.roles.update');
    Route::delete('/admin/roles/{id}', [App\Http\Controllers\RoleController::class, 'destroy'])
        ->name('admin.roles.destroy');
    
    // Permission management
    Route::get('/admin/permissions', [PermissionController::class, 'index'])
        ->name('admin.permissions.index');
    Route::get('/admin/permissions/create', [PermissionController::class, 'create'])
        ->name('admin.permissions.create');
    Route::post('/admin/permissions', [PermissionController::class, 'store'])
        ->name('admin.permissions.store');
    Route::get('/admin/permissions/{permission}', [PermissionController::class, 'show'])
        ->name('admin.permissions.show');
    Route::get('/admin/permissions/{permission}/edit', [PermissionController::class, 'edit'])
        ->name('admin.permissions.edit');
    Route::put('/admin/permissions/{permission}', [PermissionController::class, 'update'])
        ->name('admin.permissions.update');
    Route::delete('/admin/permissions/{permission}', [PermissionController::class, 'destroy'])
        ->name('admin.permissions.destroy');
    
    // Permission-role management
    Route::post('/admin/permissions/assign', [PermissionController::class, 'assignToRole'])
        ->name('admin.permissions.assign');
    Route::get('/admin/permissions/role/{roleId}', [PermissionController::class, 'getRolePermissions'])
        ->name('admin.permissions.role');
    Route::post('/admin/permissions/role/{roleId}/sync', [PermissionController::class, 'syncRolePermissions'])
        ->name('admin.permissions.role.sync');
});

// Update the sites routes to apply role-based restrictions
Route::middleware(['auth', 'check.status'])->group(function () {
    // Routes accessible to all logged-in users (view only)
    Route::get('/sites', [SitesController::class, 'sites'])->name('sites');
    Route::get('/sites/fetch', [SitesController::class, 'fetchSite'])->name('sites.fetch');
    Route::get('/sites/compatible-options', [SitesController::class, 'getCompatibleOptions'])
        ->name('sites.compatible-options');
});

Route::middleware(['auth', 'role:admin,editor', 'check.status'])->group(function () {
    // Site management routes (admin, editor)
    Route::post('/sites', [SitesController::class, 'storeSite'])->name('sites.store');
    Route::get('/sites/edit/{id}', [SitesController::class, 'editSite'])->name('sites.edit');
    Route::put('/sites/{id}', [SitesController::class, 'updateSite'])->name('sites.update');
    Route::delete('/sites/{id}', [SitesController::class, 'destroySite'])->name('sites.destroy');
});

require __DIR__.'/auth.php';
