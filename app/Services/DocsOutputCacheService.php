<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Filesystem\Filesystem;
use Throwable;

final readonly class DocsOutputCacheService
{
    public function __construct(
        private Filesystem $files,
    ) {}

    /**
     * @param  array<array-key, mixed>  $segments
     */
    public function key(string $namespace, array $segments = []): string
    {
        $normalizedSegments = array_map(
            fn (mixed $segment): string => $this->normalizeSegment($segment),
            $segments,
        );

        $parts = array_values(array_filter([
            $this->version(),
            trim($namespace),
            ...$normalizedSegments,
        ], static fn (string $part): bool => $part !== ''));

        return implode(':', $parts);
    }

    public function get(string $key): mixed
    {
        if (! $this->enabled()) {
            return null;
        }

        $filePath = $this->cacheFilePath($key);

        if (! is_file($filePath)) {
            return null;
        }

        $contents = file_get_contents($filePath);

        if ($contents === false || $contents === '') {
            return null;
        }

        /** @var array{version?: string, key?: string, value?: mixed}|null $payload */
        $payload = json_decode($contents, true);

        if (! is_array($payload)) {
            return null;
        }

        if (($payload['version'] ?? null) !== $this->version()) {
            return null;
        }

        if (($payload['key'] ?? null) !== $key) {
            return null;
        }

        return $payload['value'] ?? null;
    }

    public function put(string $key, mixed $value): void
    {
        if (! $this->enabled()) {
            return;
        }

        $filePath = $this->cacheFilePath($key);
        $directory = dirname($filePath);

        $this->files->ensureDirectoryExists($directory);

        $payload = [
            'version' => $this->version(),
            'created_at' => now()->toIso8601String(),
            'key' => $key,
            'value' => $value,
        ];

        $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            return;
        }

        try {
            $suffix = bin2hex(random_bytes(8));
        } catch (Throwable) {
            $suffix = uniqid('', true);
        }

        $tempFile = sprintf('%s.tmp.%s', $filePath, $suffix);
        $bytes = file_put_contents($tempFile, $encoded, LOCK_EX);

        if ($bytes === false) {
            return;
        }

        if (! @rename($tempFile, $filePath)) {
            @unlink($tempFile);
        }
    }

    public function remember(string $key, callable $resolver): mixed
    {
        if (! $this->enabled()) {
            return $resolver();
        }

        $cached = $this->get($key);

        if ($cached !== null) {
            return $cached;
        }

        return $this->withLock($key, function () use ($key, $resolver): mixed {
            $cachedAfterLock = $this->get($key);

            if ($cachedAfterLock !== null) {
                return $cachedAfterLock;
            }

            $value = $resolver();

            if ($value !== null) {
                $this->put($key, $value);
            }

            return $value;
        });
    }

    public function clear(): void
    {
        $path = $this->path();

        if ($this->files->isDirectory($path)) {
            $this->files->deleteDirectory($path);
        }

        $this->files->ensureDirectoryExists($path);
    }

    public function path(): string
    {
        $path = config('docs.cache.path');

        if (is_string($path) && $path !== '') {
            return $path;
        }

        return storage_path('framework/cache/docs-output');
    }

    private function enabled(): bool
    {
        $enabled = config('docs.cache.enabled');

        if (is_bool($enabled)) {
            return $enabled;
        }

        return ! app()->isLocal();
    }

    private function version(): string
    {
        $version = config('docs.cache.version');

        if (is_string($version) && $version !== '') {
            return $version;
        }

        return 'v1';
    }

    private function lockTimeoutSeconds(): int
    {
        $timeout = config('docs.cache.lock_timeout_seconds');

        if (is_int($timeout) && $timeout > 0) {
            return $timeout;
        }

        return 5;
    }

    private function cacheFilePath(string $key): string
    {
        $hash = sha1($key);
        $prefix = substr($hash, 0, 2);

        return sprintf('%s/%s/%s.json', $this->path(), $prefix, $hash);
    }

    private function lockFilePath(string $key): string
    {
        $hash = sha1($key);

        return sprintf('%s/.locks/%s.lock', $this->path(), $hash);
    }

    private function withLock(string $key, callable $callback): mixed
    {
        $lockFilePath = $this->lockFilePath($key);
        $this->files->ensureDirectoryExists(dirname($lockFilePath));

        $lockHandle = fopen($lockFilePath, 'c+');

        if ($lockHandle === false) {
            return $callback();
        }

        $timeoutAt = microtime(true) + $this->lockTimeoutSeconds();
        $acquired = false;

        do {
            $acquired = flock($lockHandle, LOCK_EX | LOCK_NB);

            if ($acquired) {
                break;
            }

            usleep(50_000);
        } while (microtime(true) < $timeoutAt);

        if (! $acquired) {
            fclose($lockHandle);

            return $callback();
        }

        try {
            return $callback();
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    private function normalizeSegment(mixed $segment): string
    {
        if (is_bool($segment)) {
            return $segment ? 'true' : 'false';
        }

        if ($segment === null) {
            return 'null';
        }

        if (is_scalar($segment)) {
            return (string) $segment;
        }

        $encoded = json_encode($segment, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($encoded !== false) {
            return $encoded;
        }

        return sha1(serialize($segment));
    }
}
