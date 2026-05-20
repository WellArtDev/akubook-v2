<?php

return [
    'paths' => [
        '.env.example',
        'config',
        '.github/workflows',
        'composer.json',
        'package.json',
        'playwright.config.js',
    ],
    'allow_values' => [
        '',
        'null',
        'true',
        'false',
        'password',
        'secret',
        'changeme',
        'your-api-key',
        'your-secret',
        'your-token',
        'example',
        'placeholder',
    ],
    'allow_patterns' => [
        '/^\$\{[A-Z0-9_]+\}$/',
        '/^env\(/',
        '/^base64:/',
        '/^admin@akubook\.com$/',
    ],
];
