<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager\Support;

use Kurt\Modules\Manager\Contracts\ScopeResolver;

/** Default resolver: everything is global (no per-scope overrides). */
final class NullScopeResolver implements ScopeResolver
{
    public function current(): ?Scope
    {
        return null;
    }
}
