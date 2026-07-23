<?php

declare(strict_types=1);

use Kurt\Modules\Core\Contracts\ModuleRegistry;
use Kurt\Modules\Core\Modules\ModuleManifest;

beforeEach(function () {
    app(ModuleRegistry::class)->register(
        ModuleManifest::make('blog')
            ->name('Blog')
            ->feature('comments', default: true)
            ->setting('posts_per_page', default: 15, type: 'int')
    );
});

it('lists modules with their effective state', function () {
    $this->getJson('api/modules')
        ->assertOk()
        ->assertJsonPath('data.0.slug', 'blog')
        ->assertJsonPath('data.0.name', 'Blog')
        ->assertJsonPath('data.0.enabled', true)
        ->assertJsonPath('data.0.features.comments', true)
        ->assertJsonPath('data.0.settings.posts_per_page', 15);
});

it('shows a single module', function () {
    $this->getJson('api/modules/blog')
        ->assertOk()
        ->assertJsonPath('data.slug', 'blog')
        ->assertJsonPath('data.enabled', true)
        ->assertJsonPath('data.features.comments', true)
        ->assertJsonPath('data.settings.posts_per_page', 15);
});

it('404s an unknown module', function () {
    $this->getJson('api/modules/ghost')->assertNotFound();
});

it('sets the enabled state', function () {
    $this->patchJson('api/modules/blog', ['enabled' => false])
        ->assertOk()
        ->assertJsonPath('data.enabled', false);

    $this->getJson('api/modules/blog')->assertJsonPath('data.enabled', false);
});

it('sets a feature override', function () {
    $this->patchJson('api/modules/blog/features/comments', ['enabled' => false])
        ->assertOk()
        ->assertJsonPath('data.features.comments', false);
});

it('sets a setting override', function () {
    $this->patchJson('api/modules/blog/settings/posts_per_page', ['value' => 30])
        ->assertOk()
        ->assertJsonPath('data.settings.posts_per_page', 30);
});

it('rejects an undeclared feature with 422', function () {
    $this->patchJson('api/modules/blog/features/nope', ['enabled' => true])
        ->assertStatus(422);
});

it('scopes a write without touching the global read', function () {
    $this->patchJson('api/modules/blog?scope_type=tenant&scope_id=5', ['enabled' => false])
        ->assertOk()
        ->assertJsonPath('data.enabled', false);

    // The scoped write must not change the global effective state.
    $this->getJson('api/modules/blog')->assertJsonPath('data.enabled', true);

    // Reading back with the same scope reflects the override.
    $this->getJson('api/modules/blog?scope_type=tenant&scope_id=5')
        ->assertJsonPath('data.enabled', false);
});
