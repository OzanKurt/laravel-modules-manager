<?php

declare(strict_types=1);

use Kurt\Modules\Core\Contracts\ModuleRegistry;
use Kurt\Modules\Core\Modules\ModuleManifest;
use Kurt\Modules\Manager\Facades\Modules;

it('proxies to the manager through the facade', function () {
    app(ModuleRegistry::class)->register(ModuleManifest::make('blog')->feature('comments', default: true));

    expect(Modules::feature('blog', 'comments'))->toBeTrue();
    Modules::setFeature('blog', 'comments', false);
    expect(Modules::feature('blog', 'comments'))->toBeFalse();
});
