<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;

class PwaManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'name' => config('app.name', 'AkuBook'),
            'short_name' => config('app.name', 'AkuBook'),
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#1f2937',
            'icons' => [
                [
                    'src' => URL::to('/favicon.ico'),
                    'sizes' => '192x192',
                    'type' => 'image/x-icon',
                    'purpose' => 'any',
                ],
                [
                    'src' => URL::to('/favicon.ico'),
                    'sizes' => '512x512',
                    'type' => 'image/x-icon',
                    'purpose' => 'any',
                ],
            ],
        ])->header('Content-Type', 'application/manifest+json');
    }
}
