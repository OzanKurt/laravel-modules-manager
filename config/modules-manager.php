<?php

declare(strict_types=1);

return [
    // headless | api | ui  (M2 uses headless only; M3 adds api)
    'http' => ['mode' => 'headless'],

    'cache' => [
        'enabled' => true,
        'store' => null,        // null = default cache store
        'prefix' => 'modules-manager',
        'ttl' => 3600,
    ],
];
