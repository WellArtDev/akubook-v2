<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use SplFileInfo;

class GuardConfigSecretsCommand extends Command
{
    protected $signature = 'app:guard-config-secrets {--path=* : Extra file or directory path to scan}';

    protected $description = 'Scan configuration files for secret-like committed values';

    public function handle(): int
    {
        $findings = [];

        foreach ($this->scanFiles() as $file) {
            foreach ($this->findSecrets($file) as $finding) {
                $findings[] = $finding;
            }
        }

        if (! empty($findings)) {
            $this->error('Config secret guardrail failed:');
            foreach ($findings as $finding) {
                $this->line(sprintf('- %s:%s %s=%s', $finding['file'], $finding['line'], $finding['key'], $finding['masked']));
            }

            return self::FAILURE;
        }

        $this->info('Config secret guardrail passed.');

        return self::SUCCESS;
    }

    private function scanFiles(): array
    {
        $paths = array_merge(config('secret_guard.paths', []), $this->option('path'));
        $files = [];

        foreach ($paths as $path) {
            $absolute = $this->absolutePath($path);

            if (File::isFile($absolute)) {
                $files[$absolute] = $absolute;
                continue;
            }

            if (! File::isDirectory($absolute)) {
                continue;
            }

            foreach (File::allFiles($absolute) as $file) {
                if ($this->shouldScan($file)) {
                    $files[$file->getPathname()] = $file->getPathname();
                }
            }
        }

        return array_values($files);
    }

    private function shouldScan(SplFileInfo $file): bool
    {
        return in_array($file->getExtension(), ['php', 'json', 'js', 'yml', 'yaml', 'env'], true)
            || Str::endsWith($file->getFilename(), ['.env.example']);
    }

    private function absolutePath(string $path): string
    {
        if (Str::startsWith($path, [DIRECTORY_SEPARATOR, 'C:\\', 'D:\\', 'E:\\'])) {
            return $path;
        }

        return base_path($path);
    }

    private function findSecrets(string $file): array
    {
        $findings = [];
        $lines = file($file, FILE_IGNORE_NEW_LINES) ?: [];

        foreach ($lines as $index => $line) {
            $candidate = $this->extractCandidate($line);
            if ($candidate === null) {
                continue;
            }

            [$key, $value] = $candidate;
            if (! $this->isSecretKey($key) || $this->isAllowedValue($value)) {
                continue;
            }

            $findings[] = [
                'file' => str_replace('\\', '/', Str::after($file, base_path(DIRECTORY_SEPARATOR))),
                'line' => $index + 1,
                'key' => $key,
                'masked' => $this->mask($value),
            ];
        }

        return $findings;
    }

    private function extractCandidate(string $line): ?array
    {
        $trimmed = trim($line);
        if ($trimmed === '' || Str::startsWith($trimmed, ['#', '//', '*'])) {
            return null;
        }

        if (preg_match('/^["\']?([A-Za-z0-9_.-]*(?:password|secret|token|api[_-]?key|private[_-]?key|authorization)[A-Za-z0-9_.-]*)["\']?\s*[:=]\s*["\']?([^"\',#\]]+)/i', $trimmed, $matches)) {
            return [$matches[1], trim($matches[2])];
        }

        return null;
    }

    private function isSecretKey(string $key): bool
    {
        return Str::contains(Str::lower($key), ['password', 'secret', 'token', 'api_key', 'apikey', 'api-key', 'private_key', 'private-key', 'authorization']);
    }

    private function isAllowedValue(string $value): bool
    {
        $normalized = Str::lower(trim($value, " \t\n\r\0\x0B'\""));

        if (in_array($normalized, config('secret_guard.allow_values', []), true)) {
            return true;
        }

        foreach (config('secret_guard.allow_patterns', []) as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                return true;
            }
        }

        return strlen($normalized) < 8;
    }

    private function mask(string $value): string
    {
        $value = trim($value, " \t\n\r\0\x0B'\"");

        return substr($value, 0, 2).'***'.substr($value, -2);
    }
}
