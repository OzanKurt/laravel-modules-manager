<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager\Tests;

use Illuminate\Foundation\Application;

/**
 * Boots the package in `headless` mode (the default) so the management REST
 * routes are NOT registered.
 */
abstract class HeadlessModeTestCase extends TestCase
{
    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('modules-manager.http.mode', 'headless');
    }
}
