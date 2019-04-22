<?php

declare(strict_types=1);

namespace App\Repository;

use App\Attempt\Profile\ProfileInitializerInterface;
use App\Attempt\Profile\ProfileNormalizerInterface;
use App\Attempt\Profile\ProfileProviderInterface;
use App\DataFixtures\Attempt\ProfileFixtures;
use App\Entity\Profile;
use App\Entity\Settings;
use App\Object\ObjectAccessor;
use App\Security\User\CurrentUserProviderInterface;
use App\Serializer\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ProfileRepository extends ServiceEntityRepository implements ProfileInitializerInterface, ProfileProviderInterface
{
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ProfileNormalizerInterface */
    private $profileNormalizer;

    public function __construct(RegistryInterface $registry, CurrentUserProviderInterface $currentUserProvider, NormalizerInterface $normalizer, ProfileNormalizerInterface $profileNormalizer)
    {
        parent::__construct($registry, Profile::class);

        $this->currentUserProvider = $currentUserProvider;
        $this->normalizer = $normalizer;
        $this->profileNormalizer = $profileNormalizer;
    }

    public function getCurrentProfile(): Profile
    {
        $currentUser = $this->currentUserProvider->getCurrentUserOrGuest();

        if (null === $currentUser->getProfile()) {
            $currentUser->setProfile(
                $this->findOneBy(['isPublic' => true, 'description' => ProfileFixtures::GUEST_PROFILE])
            );
            $this->getEntityManager()->flush($currentUser);
        }

        return $currentUser->getProfile();
    }

    public function isCurrentProfile(Profile $profile): bool
    {
        return $profile->isEqualTo($this->getCurrentProfile());
    }

    public function getCurrentUserProfiles(): array
    {
        return $this->findByAuthor($this->currentUserProvider->getCurrentUserOrGuest());
    }

    public function getPublicProfiles(): array
    {
        return $this->findBy(['isPublic' => true]);
    }

    public function initializeNewProfile(): Profile
    {
        /** @var Profile $profile */
        $profile = ObjectAccessor::initialize(Profile::class, [
            'author' => $this->currentUserProvider->getCurrentUserOrGuest(),
        ]);
        $profile->setDescription($this->getTitle($profile));
        $this->profileNormalizer->normalize($profile);

        return $profile;
    }

    private function getTitle(Profile $profile): string
    {
        return $profile->getDescription() ?: 'Профиль №'.$this->getNumber($profile);
    }

    private function getNumber(Profile $profile): int
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

    private function countByCurrentAuthor(): int
    {
        return $this->count(['author' => $this->currentUserProvider->getCurrentUserOrGuest()]);
    }

    /** @deprecated */
    public function getSettingsOrDefaultProfile(Settings $settings): Profile
    {
        $parameters = $this->normalizer->normalize($settings, null, ['groups' => Group::SETTINGS]);

        return $this->findOneBy(['author' => $this->currentUserProvider->getCurrentUserOrGuest()] + $parameters)
            ?? $this->findOneBy(['isPublic' => true] + $parameters);
    }
}
