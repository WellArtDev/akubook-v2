<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GuardConfigSecretsCommandTest extends TestCase
{
    private string $fixturePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixturePath = storage_path('framework/testing/secret-guard-fixture.env');
        File::ensureDirectoryExists(dirname($this->fixturePath));
    }

    protected function tearDown(): void
    {
        if (File::exists($this->fixturePath)) {
            File::delete($this->fixturePath);
        }

        parent::tearDown();
    }

    public function test_guard_passes_on_safe_placeholders(): void
    {
        File::put($this->fixturePath, "API_KEY=your-api-key\nDB_PASSWORD=password\nTOKEN=placeholder\n");

        $this->artisan('app:guard-config-secrets', ['--path' => [$this->fixturePath]])
            ->expectsOutput('Config secret guardrail passed.')
            ->assertExitCode(0);
    }

    public function test_guard_fails_on_secret_like_value(): void
    {
        File::put($this->fixturePath, "API_KEY=sk_live_verysecretvalue12345\n");

        $this->artisan('app:guard-config-secrets', ['--path' => [$this->fixturePath]])
            ->expectsOutput('Config secret guardrail failed:')
            ->assertExitCode(1);
    }
}
