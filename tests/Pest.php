<?php

declare(strict_types=1);

use Kurt\Modules\Manager\Tests\ApiModeTestCase;
use Kurt\Modules\Manager\Tests\HeadlessModeTestCase;
use Kurt\Modules\Manager\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');
uses(ApiModeTestCase::class)->in('Modes/Api');
uses(HeadlessModeTestCase::class)->in('Modes/Headless');
