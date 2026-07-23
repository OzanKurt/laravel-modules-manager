<?php

declare(strict_types=1);

use Kurt\Modules\Core\Contracts\ModuleRegistry;
use Kurt\Modules\Core\Modules\ModuleManifest;
use Kurt\Modules\Manager\Exceptions\UnknownModuleTarget;
use Kurt\Modules\Manager\Models\ModuleState;
use Kurt\Modules\Manager\ModuleManager;
use Kurt\Modules\Manager\Support\Scope;

beforeEach(function () {
    app(ModuleRegistry::class)->register(
        ModuleManifest::make('blog')->feature('comments', default: true)->setting('posts_per_page', default: 15, type: 'int')
    );
});

it('writes and reads back an override (global and scoped)', function () {
    $m = app(ModuleManager::class);

    $m->setEnabled('blog', false);
    $m->setFeature('blog', 'comments', false);
    $m->setSetting('blog', 'posts_per_page', 30, new Scope('tenant', 5));

    expect($m->enabled('blog'))->toBeFalse()
        ->and($m->feature('blog', 'comments'))->toBeFalse()
        ->and($m->setting('blog', 'posts_per_page', new Scope('tenant', 5)))->toBe(30)
        ->and($m->setting('blog', 'posts_per_page'))->toBe(15); // global still default
});

it('upserts rather than duplicating on repeated writes', function () {
    $m = app(ModuleManager::class);
    $m->setEnabled('blog', false);
    $m->setEnabled('blog', true);

    expect(ModuleState::where('module', 'blog')->where('kind', 'state')->count())->toBe(1)
        ->and($m->enabled('blog'))->toBeTrue();
});

it('rejects writing to an undeclared module/feature/setting', function () {
    $m = app(ModuleManager::class);
    expect(fn () => $m->setEnabled('ghost', true))->toThrow(UnknownModuleTarget::class);
    expect(fn () => $m->setFeature('blog', 'nope', true))->toThrow(UnknownModuleTarget::class);
    expect(fn () => $m->setSetting('blog', 'nope', 1))->toThrow(UnknownModuleTarget::class);
});
