<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager\Facades;

use Illuminate\Support\Facades\Facade;
use Kurt\Modules\Manager\ModuleManager;

/**
 * @method static bool enabled(string $slug, ?\Kurt\Modules\Manager\Support\Scope $scope = null)
 * @method static bool feature(string $slug, string $key, ?\Kurt\Modules\Manager\Support\Scope $scope = null)
 * @method static mixed setting(string $slug, string $key, ?\Kurt\Modules\Manager\Support\Scope $scope = null)
 * @method static void setEnabled(string $slug, bool $value, ?\Kurt\Modules\Manager\Support\Scope $scope = null)
 * @method static void setFeature(string $slug, string $key, bool $value, ?\Kurt\Modules\Manager\Support\Scope $scope = null)
 * @method static void setSetting(string $slug, string $key, mixed $value, ?\Kurt\Modules\Manager\Support\Scope $scope = null)
 *
 * @see ModuleManager
 */
final class Modules extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'modules-manager';
    }
}
