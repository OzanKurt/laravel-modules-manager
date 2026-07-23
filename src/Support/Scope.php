<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager\Support;

/** Immutable identifier of a management scope (e.g. tenant:5). */
final class Scope
{
    public function __construct(
        public readonly string $type,
        public readonly int|string $id,
    ) {}

    /** Stable cache/identity key for this scope. */
    public function key(): string
    {
        return "{$this->type}:{$this->id}";
    }
}
