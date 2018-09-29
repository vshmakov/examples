<?php

namespace App\Repository;

use App\Entity\Profile;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Service\UserLoader;

class ProfileRepository extends ServiceEntityRepository
{
    use BaseTrait;
    private $userLoader;

    public function __construct(RegistryInterface $registry, UserLoader $userLoader)
    {
        parent::__construct($registry, Profile::class);
        $this->userLoader = $userLoader;
    }

    public function findOneByAuthor(User $user)
    {
        return $this->getValue(
            $this->createQuery('select p from App:Profile p
where p.author = :u')
                ->setParameter('u', $user)
        );
    }

    public function findOneByUser(User $user)
    {
        return $this->getValue(
            $this->createQuery('select p from App:Profile p
join p.users u
where u = :u
')->setParameter('u', $user)
        );
    }

    public function findOnePublic()
    {
        return $this->getValue(
            $this->createQuery('select p from App:Profile p
where p.isPublic = true
')
        );
    }

    public function findByCurrentAuthor()
    {
        return $this->findByAuthor($this->userLoader->getUser());
    }

    public function getTitle(Profile $profile)
    {
        return $profile->getDescription() ? : 'Профиль №' . $this->getNumber($profile);
    }

    public function countByCurrentAuthor()
    {
        return $this->count(['author' => $this->userLoader->getUser()]);
    }

    public function getNumber(Profile $profile)
    {
        return ($profile->getId()) ? $this->getValue(
            $this->createQuery('select count(p) from App:Profile p
where p.author =:a and p.id <= :id')
                ->setParameters([
                    'a' => $profile->getAuthor(),
                    'id' => $p->getId()
                ])
        ) : $this->countByCurrentAuthor() + 1;
    }

    public function getNewByCurrentUser()
    {
        $profile = (new Profile);

        return $profile->SetDescription($this->getTitle($profile))
            ->setAuthor($this->userLoader->getUser());
    }


    public function findByCurrentUserTeacher()
    {
        $user = $this->userLoader->getUser();

        if (!$user->hasTeacher()) {
            return [];
        }

        return $this->findByAuthor($user->getTeacher());
    }
}
