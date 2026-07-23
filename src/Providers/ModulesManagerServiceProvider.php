<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager\Providers;

use Kurt\Modules\Manager\Contracts\ScopeResolver;
use Kurt\Modules\Manager\ModuleManager;
use Kurt\Modules\Manager\Support\NullScopeResolver;
use Kurt\Modules\Core\Contracts\ModuleRegistry;
use Kurt\Modules\Core\Providers\PackageServiceProvider;
use Spatie\LaravelPackageTools\Package;

final class ModulesManagerServiceProvider extends PackageServiceProvider
{
    protected function module(): string
    {
        return 'modules-manager';
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-modules-manager')
            ->hasConfigFile('modules-manager')
            ->hasMigration('2026_07_23_000000_create_module_states_table');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ScopeResolver::class, fn () => new NullScopeResolver());
        $this->app->singleton(ModuleManager::class, fn ($app) => new ModuleManager(
            $app->make(ModuleRegistry::class),
            $app->make(ScopeResolver::class),
        ));
        $this->app->alias(ModuleManager::class, 'modules-manager');
    }
}
