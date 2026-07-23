<?php

declare(strict_types=1);

namespace Kurt\Modules\Manager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kurt\Modules\Manager\Contracts\ScopeResolver;
use Kurt\Modules\Manager\ModuleManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates a consumer module's routes by whether that module is enabled for the
 * ambient scope. A disabled module 404s, so its routes are indistinguishable
 * from routes that were never registered.
 *
 * The scope is resolved implicitly by the bound {@see ScopeResolver}
 * (via ModuleManager); this middleware deliberately reads no request params.
 *
 * Aliased as `module.enabled` by the service provider:
 *
 *   Route::middleware('module.enabled:blog')->group(...);
 */
final class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        if (! app(ModuleManager::class)->enabled($slug)) {
            abort(404);
        }

        return $next($request);
    }
}
