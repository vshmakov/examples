<?php

namespace App\Utils\Cache;

use Psr\SimpleCache\CacheInterface;

class GlobalCache extends AbstractCache
{
    public function __construct(CacheInterface $simpleCache)
    {
        $this->storage = $simpleCache;
    }

    public function has($key): bool
    {
        return $this->works ? $this->storage->has($this->processKey($key)) : false;
    }
}
