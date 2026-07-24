<?php

declare(strict_types=1);

use Kurt\Modules\Core\Contracts\ModuleRegistry;
use Kurt\Modules\Core\Modules\ModuleManifest;
use Kurt\Modules\Manager\Models\ModuleState;
use Kurt\Modules\Manager\ModuleManager;

beforeEach(function () {
    app(ModuleRegistry::class)->register(
        ModuleManifest::make('blog')
            ->enabledByDefault(true)
            ->feature('comments', default: true)
            ->setting('posts_per_page', default: 15, type: 'int')
    );
});

/** Change a stored row straight in the DB, bypassing the manager (and its cache). */
function outOfBandUpdate(string $kind, ?string $key, mixed $value): void
{
    ModuleState::query()
        ->where('scope_type', null)
        ->where('scope_id', null)
        ->where('module', 'blog')
        ->where('kind', $kind)
        ->where('key', $key)
        ->update(['value' => json_encode(['v' => $value])]);
}

it('serves the first read from cache even after the DB row changes out of band', function () {
    ModuleState::create(['scope_type' => null, 'scope_id' => null, 'module' => 'blog', 'kind' => 'feature', 'key' => 'comments', 'value' => ['v' => false]]);

    $m = app(ModuleManager::class);

    expect($m->feature('blog', 'comments'))->toBeFalse(); // primes the cache

    outOfBandUpdate('feature', 'comments', true);

    expect($m->feature('blog', 'comments'))->toBeFalse(); // still the cached value
});

it('reflects a write made through the manager (put invalidates the key)', function () {
    $m = app(ModuleManager::class);

    expect($m->feature('blog', 'comments'))->toBeTrue(); // caches the "no override" result

    $m->setFeature('blog', 'comments', false);

    expect($m->feature('blog', 'comments'))->toBeFalse(); // invalidated, reads the new row
});

it('a write to one key does not invalidate an unrelated cached key', function () {
    ModuleState::create(['scope_type' => null, 'scope_id' => null, 'module' => 'blog', 'kind' => 'feature', 'key' => 'comments', 'value' => ['v' => false]]);

    $m = app(ModuleManager::class);

    expect($m->feature('blog', 'comments'))->toBeFalse(); // caches the feature row

    outOfBandUpdate('feature', 'comments', true); // cache is now stale on purpose

    $m->setSetting('blog', 'posts_per_page', 99); // writes/forgets a DIFFERENT key

    expect($m->feature('blog', 'comments'))->toBeFalse() // feature cache untouched
        ->and($m->setting('blog', 'posts_per_page'))->toBe(99); // setting write visible
});

it('never serves stale data when caching is disabled', function () {
    config()->set('modules-manager.cache.enabled', false);

    ModuleState::create(['scope_type' => null, 'scope_id' => null, 'module' => 'blog', 'kind' => 'feature', 'key' => 'comments', 'value' => ['v' => false]]);

    $m = app(ModuleManager::class);

    expect($m->feature('blog', 'comments'))->toBeFalse();

    outOfBandUpdate('feature', 'comments', true);

    expect($m->feature('blog', 'comments'))->toBeTrue(); // no cache: reads fresh from DB
});
