<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Kurt\Modules\Manager\Http\Controllers\ModuleController;

/*
|--------------------------------------------------------------------------
| Modules Manager REST API
|--------------------------------------------------------------------------
|
| Loaded by PackageServiceProvider::registerModuleApi(), already wrapped in
| the module's route group (prefix `api/modules`, base middleware,
| `modules-manager-api` throttle, `modules-manager.api.` name prefix). Only
| headless mode leaves it unregistered.
|
| This is a MANAGEMENT surface: reads and writes alike expose/alter every
| module's state, so the auth middleware gates every route (not just writes).
|
*/

$auth = config('modules-manager.http.auth_middleware', ['auth']);

Route::middleware($auth)->group(function () {
    Route::get('', [ModuleController::class, 'index'])->name('modules.index');
    Route::get('{slug}', [ModuleController::class, 'show'])->name('modules.show');
    Route::patch('{slug}', [ModuleController::class, 'setEnabled'])->name('modules.setEnabled');
    Route::patch('{slug}/features/{key}', [ModuleController::class, 'setFeature'])->name('modules.setFeature');
    Route::patch('{slug}/settings/{key}', [ModuleController::class, 'setSetting'])->name('modules.setSetting');
});
