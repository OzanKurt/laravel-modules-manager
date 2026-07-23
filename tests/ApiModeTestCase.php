<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager\Tests;

use Illuminate\Foundation\Application;

/**
 * Boots the package in `api` mode so the management REST routes register.
 *
 * The mode MUST be set here (before boot) rather than mid-test: route
 * registration happens once, during the provider's boot phase. The auth
 * middleware is stripped so tests exercise the endpoints without a real guard.
 */
abstract class ApiModeTestCase extends TestCase
{
    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('modules-manager.http.mode', 'api');
        $app['config']->set('modules-manager.http.auth_middleware', []);
    }
}
