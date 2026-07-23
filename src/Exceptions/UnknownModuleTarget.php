<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager\Exceptions;

use RuntimeException;

final class UnknownModuleTarget extends RuntimeException
{
    public static function module(string $slug): self
    {
        return new self("Module [{$slug}] is not registered.");
    }

    public static function feature(string $slug, string $key): self
    {
        return new self("Module [{$slug}] does not declare feature [{$key}].");
    }

    public static function setting(string $slug, string $key): self
    {
        return new self("Module [{$slug}] does not declare setting [{$key}].");
    }
}
