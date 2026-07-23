<?php

declare(strict_types=1);

return [
    // headless | api | ui  (M2 uses headless only; M3 adds api)
    'http' => [
        'mode' => 'headless',

        // REST management surface. Read by the core ApiRouteGroup when the
        // module is in api/ui mode. This is a management API, so every route
        // (reads included) gets the auth middleware.
        'prefix' => 'api/modules',
        'middleware' => ['api'],
        'auth_middleware' => ['auth'],
    ],

    'cache' => [
        'enabled' => true,
        'store' => null,        // null = default cache store
        'prefix' => 'modules-manager',
        'ttl' => 3600,
    ],
];
