<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $scope_type
 * @property string|null $scope_id
 * @property string $module
 * @property string $kind
 * @property string|null $key
 * @property mixed $value
 */
final class ModuleState extends Model
{
    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = ['value' => 'array'];
}
