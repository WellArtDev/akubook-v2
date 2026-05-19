<?php

namespace Tests\Feature;

use Tests\TestCase;

class ServiceWorkerTest extends TestCase
{
    public function test_service_worker_endpoint_returns_javascript(): void
    {
        $response = $this->get(route('pwa.service-worker'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/javascript; charset=UTF-8');
        $response->assertSee("const CACHE_NAME = 'akubook-shell-v1';", false);
        $response->assertSee("self.addEventListener('install'", false);
        $response->assertSee("self.addEventListener('fetch'", false);
    }
}
