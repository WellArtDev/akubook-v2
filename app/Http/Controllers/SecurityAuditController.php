<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class SecurityAuditController extends Controller
{
    public function index(Request $request)
    {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            $methods = collect($route->methods())
                ->reject(fn ($method) => in_array($method, ['HEAD', 'OPTIONS'], true))
                ->values()
                ->all();

            $uri = '/' . ltrim($route->uri(), '/');
            $middlewares = collect($route->gatherMiddleware())->values()->all();
            $isMutation = collect($methods)->contains(fn ($method) => in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true));
            $isAuthProtected = collect($middlewares)->contains(fn ($middleware) => $middleware === 'auth' || Str::startsWith($middleware, 'auth:'));

            return [
                'name' => $route->getName() ?: '-',
                'uri' => $uri,
                'methods' => $methods,
                'middlewares' => $middlewares,
                'is_mutation' => $isMutation,
                'is_auth_protected' => $isAuthProtected,
            ];
        });

        $publicAllowlist = [
            '/',
            '/login',
            '/register',
            '/forgot-password',
            '/manifest.webmanifest',
            '/service-worker.js',
        ];

        $publicRoutes = $routes->filter(fn ($route) => !$route['is_auth_protected'])->values();

        $allowedPublicRoutes = $publicRoutes->filter(function ($route) use ($publicAllowlist) {
            return collect($publicAllowlist)->contains(fn ($allowed) => $route['uri'] === $allowed || Str::startsWith($route['uri'], rtrim($allowed, '*')));
        })->values();

        $unexpectedPublicRoutes = $publicRoutes->reject(function ($route) use ($allowedPublicRoutes) {
            return $allowedPublicRoutes->contains(fn ($allowed) => $allowed['uri'] === $route['uri'] && $allowed['name'] === $route['name']);
        })->values();

        $mutationRoutes = $routes->filter(fn ($route) => $route['is_mutation'])->values();
        $unprotectedMutations = $mutationRoutes->filter(fn ($route) => !$route['is_auth_protected'])->values();

        return Inertia::render('SecurityAudit/Index', [
            'summary' => [
                'total_routes' => $routes->count(),
                'public_routes' => $publicRoutes->count(),
                'auth_routes' => $routes->count() - $publicRoutes->count(),
                'mutation_routes' => $mutationRoutes->count(),
                'unprotected_mutations' => $unprotectedMutations->count(),
                'unexpected_public_routes' => $unexpectedPublicRoutes->count(),
            ],
            'allowedPublicRoutes' => $allowedPublicRoutes,
            'unexpectedPublicRoutes' => $unexpectedPublicRoutes,
            'unprotectedMutations' => $unprotectedMutations,
        ]);
    }
}
