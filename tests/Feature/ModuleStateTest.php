<?php

declare(strict_types=1);

use Kurt\Modules\Manager\Models\ModuleState;

it('persists a module state row with a json value', function () {
    ModuleState::create([
        'scope_type' => null, 'scope_id' => null,
        'module' => 'blog', 'kind' => 'setting', 'key' => 'posts_per_page',
        'value' => ['v' => 15],
    ]);

    $row = ModuleState::firstOrFail();
    expect($row->module)->toBe('blog')
        ->and($row->kind)->toBe('setting')
        ->and($row->value)->toBe(['v' => 15]);
});
