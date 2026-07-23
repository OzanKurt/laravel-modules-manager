<?php

declare(strict_types=1);

use Kurt\Modules\Core\Contracts\ModuleRegistry;
use Kurt\Modules\Core\Modules\ModuleManifest;
use Kurt\Modules\Manager\Models\ModuleState;
use Kurt\Modules\Manager\ModuleManager;
use Kurt\Modules\Manager\Support\Scope;

beforeEach(function () {
    app(ModuleRegistry::class)->register(
        ModuleManifest::make('blog')
            ->enabledByDefault(true)
            ->feature('comments', default: true)
            ->setting('posts_per_page', default: 15, type: 'int')
    );
});

it('falls back to manifest defaults with no DB rows', function () {
    $m = app(ModuleManager::class);
    expect($m->enabled('blog'))->toBeTrue()
        ->and($m->feature('blog', 'comments'))->toBeTrue()
        ->and($m->setting('blog', 'posts_per_page'))->toBe(15);
});

it('unknown module is disabled and unknown keys use hard defaults', function () {
    $m = app(ModuleManager::class);
    expect($m->enabled('nope'))->toBeFalse()
        ->and($m->feature('blog', 'missing'))->toBeFalse()
        ->and($m->setting('blog', 'missing'))->toBeNull();
});

it('global row overrides the manifest default', function () {
    ModuleState::create(['scope_type' => null, 'scope_id' => null, 'module' => 'blog', 'kind' => 'state', 'key' => null, 'value' => ['v' => false]]);
    expect(app(ModuleManager::class)->enabled('blog'))->toBeFalse();
});

it('scope row overrides the global row', function () {
    ModuleState::create(['scope_type' => null, 'scope_id' => null, 'module' => 'blog', 'kind' => 'feature', 'key' => 'comments', 'value' => ['v' => false]]);
    ModuleState::create(['scope_type' => 'tenant', 'scope_id' => '5', 'module' => 'blog', 'kind' => 'feature', 'key' => 'comments', 'value' => ['v' => true]]);

    $m = app(ModuleManager::class);
    expect($m->feature('blog', 'comments'))->toBeFalse()                       // global
        ->and($m->feature('blog', 'comments', new Scope('tenant', 5)))->toBeTrue(); // scope wins
});
