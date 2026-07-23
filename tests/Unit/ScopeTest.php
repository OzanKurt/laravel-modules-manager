<?php

declare(strict_types=1);

use Kurt\Modules\Manager\Contracts\ScopeResolver;
use Kurt\Modules\Manager\Support\NullScopeResolver;
use Kurt\Modules\Manager\Support\Scope;

it('builds a scope key', function () {
    expect((new Scope('tenant', 5))->key())->toBe('tenant:5');
});

it('resolves the null (global) scope by default', function () {
    expect(app(ScopeResolver::class))->toBeInstanceOf(NullScopeResolver::class)
        ->and(app(ScopeResolver::class)->current())->toBeNull();
});
