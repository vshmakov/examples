<?php

namespace App\Repository;

use App\Entity\BaseProfile;
use App\Entity\Profile;
use App\Entity\User;
use App\Service\UserLoader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

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
        return $profile->getDescription() ?: 'Профиль №'.$this->getNumber($profile);
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
                    'id' => $p->getId(),
                ])
        ) : $this->countByCurrentAuthor() + 1;
    }

    public function getNewByCurrentUser()
    {
        $profile = (new Profile());

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

    public function findOneByCurrentAuthorOrPublicAndSettingsData(BaseProfile $settings): ? Profile
    {
        $where = array_reduce(Profile::getSettingsFields(), function (string $where, string $property): string {
            if ($where) {
                $where .= ' and ';
            }

            return $where."p.$property = :$property";
        }, '');

        $profile = $this->getValue(
            $this->createQuery("select p from App:Profile p
where p.author = :user and $where")
                ->setParameters(['user' => $this->userLoader->getUser()] + $settings->getSettings())
        );

        return $profile ?? $this->findOneBy(['isPublic' => true] + $settings->getSettings());
    }
}
