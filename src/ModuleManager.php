<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Kurt\Modules\Core\Contracts\ModuleRegistry;
use Kurt\Modules\Manager\Contracts\ScopeResolver;
use Kurt\Modules\Manager\Exceptions\UnknownModuleTarget;
use Kurt\Modules\Manager\Models\ModuleState;
use Kurt\Modules\Manager\Support\Scope;

final class ModuleManager
{
    /**
     * Cached under a row's exact identity in place of `null` when the DB has no
     * row: distinguishes "known absent" (skip the query) from a cache miss. A
     * stored row is always an array, so this string can never collide with one.
     */
    private const ABSENT = '__module_state_absent__';

    public function __construct(
        private readonly ModuleRegistry $registry,
        private readonly ScopeResolver $scopes,
        private readonly CacheRepository $cache,
        private readonly bool $cacheEnabled,
        private readonly string $cachePrefix,
        private readonly int $cacheTtl,
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

    public function setEnabled(string $slug, bool $value, ?Scope $scope = null): void
    {
        if (! $this->registry->has($slug)) {
            throw UnknownModuleTarget::module($slug);
        }

        $this->put($slug, 'state', null, $value, $scope);
    }

    public function setFeature(string $slug, string $key, bool $value, ?Scope $scope = null): void
    {
        $manifest = $this->registry->get($slug);
        if ($manifest === null || ! $manifest->hasFeature($key)) {
            throw UnknownModuleTarget::feature($slug, $key);
        }

        $this->put($slug, 'feature', $key, $value, $scope);
    }

    public function setSetting(string $slug, string $key, mixed $value, ?Scope $scope = null): void
    {
        $manifest = $this->registry->get($slug);
        if ($manifest === null || ! $manifest->hasSetting($key)) {
            throw UnknownModuleTarget::setting($slug, $key);
        }

        $this->put($slug, 'setting', $key, $value, $scope);
    }

    private function put(string $slug, string $kind, ?string $key, mixed $value, ?Scope $scope): void
    {
        $scope ??= $this->scopes->current();
        $scopeType = $scope?->type;
        $scopeId = $scope === null ? null : (string) $scope->id;

        ModuleState::query()->updateOrCreate(
            [
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
                'module' => $slug,
                'kind' => $kind,
                'key' => $key,
            ],
            ['value' => ['v' => $value]],
        );

        if ($this->cacheEnabled) {
            $this->cache->forget($this->cacheKey($slug, $kind, $key, $scopeType, $scopeId));
        }
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

    /**
     * The stored row for one exact identity, served from cache when enabled. The
     * cache maps 1:1 to a DB row identity, so it stays coherent as long as
     * {@see put()} forgets the identity it writes.
     *
     * @return array<string, mixed>|null
     */
    private function row(string $slug, string $kind, ?string $key, ?string $scopeType, ?string $scopeId): ?array
    {
        if (! $this->cacheEnabled) {
            return $this->fetchRow($slug, $kind, $key, $scopeType, $scopeId);
        }

        $cacheKey = $this->cacheKey($slug, $kind, $key, $scopeType, $scopeId);
        $cached = $this->cache->get($cacheKey);

        if ($cached === self::ABSENT) {
            return null;
        }

        if (is_array($cached)) {
            /** @var array<string, mixed> $cached */
            return $cached;
        }

        $row = $this->fetchRow($slug, $kind, $key, $scopeType, $scopeId);
        $this->cache->put($cacheKey, $row ?? self::ABSENT, $this->cacheTtl);

        return $row;
    }

    /** @return array<string, mixed>|null */
    private function fetchRow(string $slug, string $kind, ?string $key, ?string $scopeType, ?string $scopeId): ?array
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

    /** Deterministic cache key for one exact row identity, with a stable token for nulls. */
    private function cacheKey(string $slug, string $kind, ?string $key, ?string $scopeType, ?string $scopeId): string
    {
        $token = static fn (?string $part): string => $part ?? '__NULL__';

        return sprintf(
            '%s:%s|%s|%s|%s|%s',
            $this->cachePrefix,
            $token($scopeType),
            $token($scopeId),
            $slug,
            $kind,
            $token($key),
        );
    }
}
