<?php

namespace App\Repository;

use App\Entity\BaseProfile;
use App\Entity\Profile;
use App\Entity\User;
use App\Object\ObjectAccessor;
use App\Security\User\CurrentUserProviderInterface;
use App\Serializer\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ProfileRepository extends ServiceEntityRepository
{
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(RegistryInterface $registry, CurrentUserProviderInterface $currentUserProvider, NormalizerInterface $normalizer)
    {
        parent::__construct($registry, Profile::class);

        $this->currentUserProvider = $currentUserProvider;
        $this->normalizer = $normalizer;
    }

    public function findOneByAuthor(User $user): ?Profile
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.author = :user')
            ->getQuery()
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function findOneByUser(User $user): ?Profile
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->join('p.users', 'u')
            ->where('u = :user')
            ->getQuery()
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function findOnePublic(): ?Profile
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.isPublic = true')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function findByCurrentAuthor(): ?Profile
    {
        return $this->findByAuthor($this->currentUserProvider->getCurrentUserOrGuest());
    }

    public function getTitle(Profile $profile): string
    {
        return $profile->getDescription() ?: 'Профиль №'.$this->getNumber($profile);
    }

    private function countByCurrentAuthor(): int
    {
        return $this->count(['author' => $this->currentUserProvider->getCurrentUserOrGuest()]);
    }

    public function getNumber(Profile $profile): int
    {
        if (null === $profile->getId()) {
            return $this->countByCurrentAuthor() + 1;
        }

        return $this->createQueryBuilder('p')
            ->select('count(p)')
            ->where('p.author = :author')
            ->andWhere('p.id <= :profileId')
            ->getQuery()
            ->setParameters([
                'author' => $profile->getAuthor(),
                'profileId' => $profile->getId(),
            ])
            ->getSingleScalarResult();
    }

    public function getNewByCurrentUser(): Profile
    {
        return ObjectAccessor::initialize(Profile::class, [
            'description' => $this->getTitle($profile),
            'author' => $this->currentUserProvider->getCurrentUserOrGuest(),
        ]);
    }

    public function findByCurrentUserTeacher(): array
    {
        $user = $this->currentUserProvider->getCurrentUserOrGuest();

        if (!$user->hasTeacher()) {
            return [];
        }

        return $this->findByAuthor($user->getTeacher());
    }

    public function findOneByCurrentAuthorOrPublicAndSettingsData(BaseProfile $settings): ?Profile
    {
        $parameters = $this->normalizer->normalize($settings, null, ['groups' => Group::SETTINGS]);

        return $this->findOneBy(['author' => $this->currentUserProvider->getCurrentUserOrGuest()] + $parameters)
            ?? $this->findOneBy(['isPublic' => true] + $parameters);
    }
}
