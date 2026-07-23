<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager;

use Kurt\Modules\Core\Contracts\ModuleRegistry;
use Kurt\Modules\Manager\Contracts\ScopeResolver;
use Kurt\Modules\Manager\Models\ModuleState;
use Kurt\Modules\Manager\Support\Scope;

final class ModuleManager
{
    public function __construct(
        private readonly ModuleRegistry $registry,
        private readonly ScopeResolver $scopes,
    ) {}

    public function enabled(string $slug, ?Scope $scope = null): bool
    {
        $manifest = $this->registry->get($slug);
        if ($manifest === null) {
            return false;
        }

        $override = $this->resolveOverride($slug, 'state', null, $scope);

        return $override === null ? $manifest->isEnabledByDefault() : (bool) $override;
    }

    public function feature(string $slug, string $key, ?Scope $scope = null): bool
    {
        $manifest = $this->registry->get($slug);
        if ($manifest === null || ! $manifest->hasFeature($key)) {
            return false;
        }

        $override = $this->resolveOverride($slug, 'feature', $key, $scope);

        return $override === null ? $manifest->featureDefault($key) : (bool) $override;
    }

    public function setting(string $slug, string $key, ?Scope $scope = null): mixed
    {
        $manifest = $this->registry->get($slug);
        if ($manifest === null || ! $manifest->hasSetting($key)) {
            return null;
        }

        $override = $this->resolveOverride($slug, 'setting', $key, $scope);

        return $override === null ? $manifest->settingDefault($key) : $override;
    }

    /**
     * The stored override value ({@see ModuleState::$value} unwrapped from its
     * `['v' => ...]` envelope), preferring the active scope's row over the
     * global row. Returns null when neither exists.
     */
    private function resolveOverride(string $slug, string $kind, ?string $key, ?Scope $scope): mixed
    {
        $scope ??= $this->scopes->current();

        if ($scope !== null) {
            $scoped = $this->row($slug, $kind, $key, $scope->type, (string) $scope->id);
            if ($scoped !== null) {
                return $scoped['v'] ?? null;
            }
        }

        $global = $this->row($slug, $kind, $key, null, null);

        return $global === null ? null : ($global['v'] ?? null);
    }

    /** @return array<string, mixed>|null */
    private function row(string $slug, string $kind, ?string $key, ?string $scopeType, ?string $scopeId): ?array
    {
        /** @var ModuleState|null $state */
        $state = ModuleState::query()
            ->where('module', $slug)
            ->where('kind', $kind)
            ->where('key', $key)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->first();

        return $state?->value;
    }
}
