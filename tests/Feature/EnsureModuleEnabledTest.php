<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Kurt\Modules\Core\Contracts\ModuleRegistry;
use Kurt\Modules\Core\Modules\ModuleManifest;
use Kurt\Modules\Manager\ModuleManager;

beforeEach(function () {
    app(ModuleRegistry::class)->register(ModuleManifest::make('blog')->name('Blog'));

    Route::middleware('module.enabled:blog')->get('_test/blog', fn () => 'ok');
});

it('passes the request through when the module is enabled', function () {
    $this->get('_test/blog')->assertOk()->assertSee('ok');
});

it('404s when the module is disabled', function () {
    app(ModuleManager::class)->setEnabled('blog', false);

    $this->get('_test/blog')->assertNotFound();
});
