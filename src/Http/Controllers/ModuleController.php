<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kurt\Modules\Core\Contracts\ModuleRegistry;
use Kurt\Modules\Core\Http\Controllers\ApiController;
use Kurt\Modules\Core\Modules\ModuleManifest;
use Kurt\Modules\Manager\Exceptions\UnknownModuleTarget;
use Kurt\Modules\Manager\ModuleManager;
use Kurt\Modules\Manager\Support\Scope;

/**
 * REST management surface for module state, features, and settings.
 *
 * The registry is the authority: a slug/feature/setting absent from a manifest
 * cannot be read (404) or written (422). Every payload reports the *effective*
 * value for the request's scope (query params `scope_type` + `scope_id`), or the
 * global scope when they are absent.
 */
final class ModuleController extends ApiController
{
    public function __construct(
        private readonly ModuleManager $manager,
        private readonly ModuleRegistry $registry,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $scope = $this->scopeFromRequest($request);

        $modules = array_map(
            fn (ModuleManifest $manifest): array => $this->modulePayload($manifest, $scope),
            array_values($this->registry->all()),
        );

        return $this->respond($modules);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        if (! $this->registry->has($slug)) {
            abort(404);
        }

        return $this->respond($this->modulePayload($this->manifest($slug), $this->scopeFromRequest($request)));
    }

    public function setEnabled(Request $request, string $slug): JsonResponse
    {
        $data = $this->validate($request, ['enabled' => 'required|boolean']);
        $scope = $this->scopeFromRequest($request);

        try {
            $this->manager->setEnabled($slug, (bool) $data['enabled'], $scope);
        } catch (UnknownModuleTarget $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return $this->respond($this->modulePayload($this->manifest($slug), $scope));
    }

    public function setFeature(Request $request, string $slug, string $key): JsonResponse
    {
        $data = $this->validate($request, ['enabled' => 'required|boolean']);
        $scope = $this->scopeFromRequest($request);

        try {
            $this->manager->setFeature($slug, $key, (bool) $data['enabled'], $scope);
        } catch (UnknownModuleTarget $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return $this->respond($this->modulePayload($this->manifest($slug), $scope));
    }

    public function setSetting(Request $request, string $slug, string $key): JsonResponse
    {
        $data = $this->validate($request, ['value' => 'present']);
        $scope = $this->scopeFromRequest($request);

        try {
            $this->manager->setSetting($slug, $key, $data['value'], $scope);
        } catch (UnknownModuleTarget $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return $this->respond($this->modulePayload($this->manifest($slug), $scope));
    }

    /**
     * The effective single-module shape, DRY across index/show/writes.
     *
     * @return array{
     *     slug: string,
     *     name: string,
     *     version: string|null,
     *     description: string|null,
     *     enabled: bool,
     *     features: array<string, bool>,
     *     settings: array<string, mixed>,
     * }
     */
    private function modulePayload(ModuleManifest $manifest, ?Scope $scope): array
    {
        $slug = $manifest->slug();

        $features = [];
        foreach (array_keys($manifest->features()) as $key) {
            $features[$key] = $this->manager->feature($slug, $key, $scope);
        }

        $settings = [];
        foreach (array_keys($manifest->settings()) as $key) {
            $settings[$key] = $this->manager->setting($slug, $key, $scope);
        }

        return [
            'slug' => $slug,
            'name' => $manifest->getName(),
            'version' => $manifest->getVersion(),
            'description' => $manifest->getDescription(),
            'enabled' => $this->manager->enabled($slug, $scope),
            'features' => $features,
            'settings' => $settings,
        ];
    }

    /**
     * Resolve the request's management scope, or null for the global scope.
     * Both `scope_type` and `scope_id` must be present to build a scope.
     */
    private function scopeFromRequest(Request $request): ?Scope
    {
        $type = $request->query('scope_type');
        $id = $request->query('scope_id');

        if (is_string($type) && $type !== '' && is_string($id) && $id !== '') {
            return new Scope($type, $id);
        }

        return null;
    }

    /**
     * Fetch a manifest known to exist (callers guard with the registry first).
     */
    private function manifest(string $slug): ModuleManifest
    {
        $manifest = $this->registry->get($slug);

        if ($manifest === null) {
            abort(404);
        }

        return $manifest;
    }
}
