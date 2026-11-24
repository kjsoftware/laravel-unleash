<?php

namespace JWebb\Unleash\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\CacheInterface;
use Unleash\Client\Enum\CacheKey;

/**
 * Thanks to leo108 for the `SimpleCacheBridge.php` gist
 * https://gist.github.com/leo108/bd7559654c52000cc9774a80b072c629
 */
class CacheBridge implements CacheInterface
{
    /**
     * Incomplete class names already reported by this process, to avoid flooding the log on every flag check.
     *
     * @var array<string, true>
     */
    private static array $reportedIncompleteClasses = [];

    public function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::memo()->get($key, $default);

        if ($key !== CacheKey::FEATURES || $value === $default) {
            return $value;
        }

        $incompleteClasses = [];
        $this->collectIncompleteClasses($value, $incompleteClasses);

        if ($incompleteClasses === []) {
            return $value;
        }

        $this->delete($key);
        $this->reportIncompleteClasses($key, array_keys($incompleteClasses));

        return $default;
    }

    /**
     * @param array<string, true> $classes
     */
    private function collectIncompleteClasses(mixed $value, array &$classes): void
    {
        if ($value instanceof \__PHP_Incomplete_Class) {
            $className = ((array) $value)['__PHP_Incomplete_Class_Name'] ?? 'unknown';
            $classes[is_string($className) ? $className : 'unknown'] = true;
        }

        if (! is_array($value) && ! is_object($value)) {
            return;
        }

        foreach ((array) $value as $item) {
            $this->collectIncompleteClasses($item, $classes);
        }
    }

    /**
     * @param list<string> $classes
     */
    private function reportIncompleteClasses(string $key, array $classes): void
    {
        sort($classes);
        $fingerprint = implode(',', $classes);

        if (isset(self::$reportedIncompleteClasses[$fingerprint])) {
            return;
        }

        self::$reportedIncompleteClasses[$fingerprint] = true;

        Log::warning('Unleash feature cache contains incomplete classes; the cache entry is discarded and features are fetched again. Add these classes to cache.serializable_classes.', [
            'cache_key' => $key,
            'classes' => $classes,
        ]);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param null|int|\DateInterval $ttl
     * @return bool
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        Cache::memo()->put($key, $value, $ttl);

        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return Cache::memo()->forget($key);
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        return Cache::memo()->flush();
    }

    /**
     * @param iterable $keys
     * @param mixed $default
     * @return iterable
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return Cache::memo()->many($keys);
    }

    /**
     * @param iterable $values
     * @param null|int|\DateInterval $ttl
     * @return bool
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        Cache::memo()->putMany($values, $ttl);

        return true;
    }

    /**
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return Cache::memo()->has($key);
    }
}
