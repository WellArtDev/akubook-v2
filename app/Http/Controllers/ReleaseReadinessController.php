<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class ReleaseReadinessController extends Controller
{
    public function __invoke(Request $request)
    {
        $routes = collect(Route::getRoutes()->getRoutesByName());

        $checks = collect([
            $this->routeCheck($routes, 'pwa.manifest', 'PWA Manifest route available'),
            $this->routeCheck($routes, 'pwa.service-worker', 'Service Worker route available'),
            $this->routeCheck($routes, 'security-audit.index', 'Security Audit route available'),
            $this->routeCheck($routes, 'role-dashboard.index', 'Role Dashboard route available'),
            $this->routeCheck($routes, 'custom-reports.index', 'Custom Reports route available'),
            $this->envCheck('APP_KEY', !empty(config('app.key')), 'APP_KEY configured'),
            $this->envCheck('APP_DEBUG', !(app()->environment('production') && config('app.debug')), 'APP_DEBUG false in production'),
        ]);

        return Inertia::render('ReleaseReadiness/Index', [
            'checks' => $checks->values(),
            'summary' => [
                'total' => $checks->count(),
                'passed' => $checks->where('passed', true)->count(),
                'failed' => $checks->where('passed', false)->count(),
            ],
            'generated_at' => now()->toDateTimeString(),
        ]);
    }

    private function routeCheck($routes, string $name, string $label): array
    {
        return [
            'type' => 'route',
            'key' => $name,
            'label' => $label,
            'passed' => $routes->has($name),
            'note' => $routes->has($name) ? 'OK' : 'Missing route',
        ];
    }

    private function envCheck(string $key, bool $passed, string $label): array
    {
        return [
            'type' => 'env',
            'key' => $key,
            'label' => $label,
            'passed' => $passed,
            'note' => $passed ? 'OK' : 'Check failed',
        ];
    }
}
