<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class ServiceWorkerController extends Controller
{
    public function __invoke(): Response
    {
        $script = <<<'JS'
const CACHE_NAME = 'akubook-shell-v1';
const APP_SHELL = ['/', '/manifest.webmanifest'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((names) => Promise.all(names.filter((name) => name !== CACHE_NAME).map((name) => caches.delete(name)))).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const requestUrl = new URL(event.request.url);
    if (requestUrl.origin !== self.location.origin) return;

    const isDocument = event.request.mode === 'navigate' || event.request.destination === 'document';

    if (isDocument) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    const cloned = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, cloned));
                    return response;
                })
                .catch(() => caches.match(event.request).then((cached) => cached || caches.match('/')))
        );
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) return cached;
            return fetch(event.request).then((response) => {
                const cloned = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, cloned));
                return response;
            });
        })
    );
});
JS;

        return response($script, 200)->header('Content-Type', 'application/javascript; charset=UTF-8');
    }
}
