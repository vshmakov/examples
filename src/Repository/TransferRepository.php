<?php

namespace App\Repository;

use App\Entity\Transfer;
use App\Service\UserLoader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TransferRepository extends ServiceEntityRepository
{
    use BaseTrait;

    private $userLoader;

    public function __construct(RegistryInterface $registry, UserLoader $userLoader)
    {
        parent::__construct($registry, Transfer::class);
        $this->userLoader = $userLoader;
    }

    public function findUnheldByCurrentUserOrNew()
    {
        return $this->findOneBy(['held' => false, 'user' => $this->userLoader->getUser()])
            ?? $this->getNewByCurrentUser();
    }

    public function getNewByCurrentUser()
    {
        $transfer = (new Transfer())
            ->setUser($this->userLoader->getUser())
            ->setLabel(randStr(32));
        $entityManager = $this->getEntityManager();
        $entityManager->persist($transfer);
        $entityManager->flush();

        return $transfer;
    }
}
