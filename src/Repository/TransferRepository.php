<?php

namespace App\Repository;

use App\Service\UserLoader;
use App\Entity\Transfer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TransferRepository extends ServiceEntityRepository
{
    use BaseTrait;

    private $ul;

    public function __construct(RegistryInterface $registry, UserLoader $ul)
    {
        parent::__construct($registry, Transfer::class);
        $this->ul = $ul;
    }

    public function findUnheldByCurrentUserOrNew()
    {
        return $this->findOneBy(['held' => false, 'user' => $this->ul->getUser()]) ?? $this->getNewByCurrentUser();
    }

    public function getNewByCurrentUser()
    {
        $e = (new Transfer())
->setUser($this->ul->getUser())
->setLabel(randStr(32));
        $em = $this->em();
        $em->persist($e);
        $em->flush();

        return $e;
    }
}
