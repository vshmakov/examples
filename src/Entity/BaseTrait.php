<?php

namespace App\Entity;

trait BaseTrait
{
    use \App\BaseTrait;

    private $entityRepository;

    public function setER($entityRepository)
    {
        return $this->setEntityRepository($entityRepository);
    }

    public function setEntityRepository($entityRepository)
    {
        $this->entityRepository = $entityRepository;

        return $this;
    }

    public function __call($v, $p = [])
    {
        if ($gs = $this->getSetter($v, $p)) {
            return $gs['ret'];
        }
        $m = entityGetter($v);
        $er = $this->entityRepository;

        if (!method_exists($er, $m)) {
            throw new \Exception(sprintf('Entity %s and %s repository does not contain %s getter', self::class, is_object($er) ? get_class($er) : 'empty', $v));
        }

        return $er->$m($this);
    }

    private function getSetter($method, $p)
    {
        foreach (['get', 'set'] as $action) {
            if (preg_match("#^$action(.+)$#", $method, $arr)) {
                $v = lcfirst($arr[1]);

                if (isset($this->$v)) {
                    if (!$g = 'get' == $action) {
                        $this->$v = $p[0];
                    }

                    return ['ret' => $g ? $this->$v : $this];
                }
            }
        }

        return false;
    }
}
