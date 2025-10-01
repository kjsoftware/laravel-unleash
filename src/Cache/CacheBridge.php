<?php

namespace JWebb\Unleash\Cache;

use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\CacheInterface;

/**
 * Thanks to leo108 for the `SimpleCacheBridge.php` gist
 * https://gist.github.com/leo108/bd7559654c52000cc9774a80b072c629
 */
class CacheBridge implements CacheInterface
{
    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::memo()->get($key, $default);
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
