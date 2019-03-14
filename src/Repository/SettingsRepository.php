<?php

namespace App\Repository;

use App\Entity\Profile;
use App\Entity\Settings;
use App\Entity\User;
use App\Security\User\CurrentUserProviderInterface;
use App\Serializer\Group;
use App\Settings\SettingsProviderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SettingsRepository extends ServiceEntityRepository implements SettingsProviderInterface
{
    use BaseTrait;
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var ObjectNormalizer */
    private $normalizer;

    public function __construct(RegistryInterface $registry, CurrentUserProviderInterface $currentUserProvider, ObjectNormalizer $normalizer)
    {
        parent::__construct($registry, Settings::class);

        $this->currentUserProvider = $currentUserProvider;
        $this->normalizer = $normalizer;
    }

    public function getNewByCurrentUser(): Settings
    {
        $currentUser = $this->currentUserProvider->getCurrentUserOrGuest()
            ->setEntityRepository($this->getEntityRepository(User::class));
        $profile = $currentUser->getCurrentProfile();

        return $this->getSettingsByProfileOrCreate($profile);
    }

    public function getSettingsByProfileOrCreate(Profile $profile): Settings
    {
        $settingsData = $this->normalizer->normalize($profile, null, ['groups' => Group::SETTINGS]);

        if ($settings = $this->findOneBy($settingsData)) {
            return $settings;
        }

        $settings = $this->normalizer->denormalize($settingsData, Settings::class);
        $entityManager = $this->getEntityManager();
        $entityManager->persist($settings);
        $entityManager->flush();

        return $settings;
    }
}
