<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaManifestTest extends TestCase
{
    public function test_manifest_endpoint_returns_required_fields(): void
    {
        $response = $this->get(route('pwa.manifest'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/manifest+json');
        $response->assertJsonStructure([
            'name',
            'short_name',
            'start_url',
            'display',
            'background_color',
            'theme_color',
            'icons',
        ]);
        $response->assertJsonPath('display', 'standalone');
        $response->assertJsonPath('icons.0.sizes', '192x192');
    }
}
