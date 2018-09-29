<?php

namespace App\Entity;

use Doctrine\Common\Inflector\Inflector;

trait BaseTrait
{
    use \App\BaseTrait;

    private $entityRepository;

    public function setEntityRepository($entityRepository)
    {
        $this->entityRepository = $entityRepository;

        return $this;
    }

    public function __call($method, $params = [])
    {
        $entityRepository = $this->entityRepository;

        if (!preg_match('#^get#', $method)) {
            $method = "get_$method";
        }
        
        $getter = Inflector::camelize($method);

        if (!$entityRepository or !method_exists($entityRepository, $getter)) {
            throw new \Exception(sprintf('Entity %s and %s repository does not contain %s getter', self::class, $entityRepository ? get_class($entityRepository) : 'empty', $method));
        }

        return $entityRepository->$getter($this);
    }
}
