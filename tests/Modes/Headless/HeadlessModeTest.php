<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('does not register the management routes in headless mode', function () {
    expect(Route::has('modules-manager.api.modules.index'))->toBeFalse()
        ->and(Route::has('modules-manager.api.modules.show'))->toBeFalse();

    $this->getJson('api/modules')->assertNotFound();
});
