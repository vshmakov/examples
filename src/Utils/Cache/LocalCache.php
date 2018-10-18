<?php

namespace App\Utils\Cache;

use Doctrine\Common\Collections\ArrayCollection;

class LocalCache extends AbstractCache
{
    public function __construct()
    {
        $this->storage = new ArrayCollection();
        $this->works = true;
    }

    public function has($key): bool
    {
        return $this->works ? $this->storage->offsetExists($this->processKey($key)) : false;
    }
}
