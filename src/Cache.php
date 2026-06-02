<?php

namespace Autodiscover;

class Cache
{
    private $enabled;
    private $ttl;
    private $path;

    public function __construct(array $config)
    {
        $this->enabled = $config['cache']['enabled'] ?? true;
        $this->ttl = $config['cache']['ttl'] ?? 3600;
        $this->path = $config['cache']['path'] ?? sys_get_temp_dir() . '/autodiscover_cache';

        if ($this->enabled && !is_dir($this->path)) {
            @mkdir($this->path, 0755, true);
        }
    }

    /**
     * Get cached value
     */
    public function get(string $key): ?string
    {
        if (!$this->enabled) {
            return null;
        }

        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return null;
        }

        $mtime = filemtime($file);
        if (time() - $mtime > $this->ttl) {
            unlink($file);
            return null;
        }

        return file_get_contents($file);
    }

    /**
     * Set cache value
     */
    public function set(string $key, string $value): void
    {
        if (!$this->enabled) {
            return;
        }

        $file = $this->getFilePath($key);
        file_put_contents($file, $value);
    }

    /**
     * Clear specific cache entry
     */
    public function clear(string $key): void
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Clear all cache
     */
    public function flush(): void
    {
        if (!is_dir($this->path)) {
            return;
        }

        array_map('unlink', glob($this->path . '/*'));
    }

    /**
     * Get cache file path
     */
    private function getFilePath(string $key): string
    {
        return $this->path . '/' . md5($key) . '.cache';
    }
}
