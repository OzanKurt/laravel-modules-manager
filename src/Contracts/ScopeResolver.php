<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager\Contracts;

use Kurt\Modules\Manager\Support\Scope;

interface ScopeResolver
{
    /** The active scope, or null for the global scope. */
    public function current(): ?Scope;
}
