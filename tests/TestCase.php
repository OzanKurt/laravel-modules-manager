<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager\Tests;

use Illuminate\Foundation\Application;
use Kurt\Modules\Manager\Providers\ModulesManagerServiceProvider;
use Kurt\Modules\Core\Testing\PackageTestCase;

abstract class TestCase extends PackageTestCase
{
    /** @param  Application  $app @return array<int, class-string> */
    protected function modulePackageProviders($app): array
    {
        return [ModulesManagerServiceProvider::class];
    }

    protected function defineDatabaseMigrations(): void
    {
        parent::defineDatabaseMigrations();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
