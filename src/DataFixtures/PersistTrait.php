<?php

namespace App\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;

trait PersistTrait
{
    /** @var ObjectManager */
    private $manager;

    private function persist(object $object): void
    {
        $this->manager->persist($object);
    }
}
