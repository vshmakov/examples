<?php

namespace App\Repository;

use App\Attempt\Profile\ProfileProviderInterface;
use  App\Attempt\Settings\SettingsProviderInterface;
use App\DateTime\DateTime as DT;
use App\Entity\Profile;
use App\Entity\Settings;
use App\Entity\User;
use App\Object\ObjectAccessor;
use App\Repository\Traits\BaseTrait;
use App\Security\User\CurrentUserProviderInterface;
use App\Serializer\Group;
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

    /** @var ProfileProviderInterface */
    private $profileProvider;

    public function __construct(RegistryInterface $registry, CurrentUserProviderInterface $currentUserProvider, ObjectNormalizer $normalizer, ProfileProviderInterface $profileProvider)
    {
        parent::__construct($registry, Settings::class);

        $this->currentUserProvider = $currentUserProvider;
        $this->normalizer = $normalizer;
        $this->profileProvider = $profileProvider;
    }

    public function getNewByCurrentUser(): Settings
    {
        $currentUser = $this->currentUserProvider->getCurrentUserOrGuest()
            ->setEntityRepository($this->getEntityRepository(User::class));
        $profile = $currentUser->getCurrentProfile();

        return $this->getOrCreateSettingsByProfile($profile);
    }

    public function getOrCreateSettingsByProfile(Profile $profile): Settings
    {
        $settingsData = $this->normalizer->normalize($profile, null, ['groups' => Group::SETTINGS]);

        if ($settings = $this->findOneBy($settingsData)) {
            return $settings;
        }

        $settingsData['duration'] = DT::createFromTimestamp($settingsData['duration']);
        $settings = ObjectAccessor::initialize(Settings::class, $settingsData);
        $entityManager = $this->getEntityManager();
        $entityManager->persist($settings);
        $entityManager->flush();

        return $settings;
    }

    public function getOrCreateSettingsByCurrentUserProfile(): Settings
    {
        return $this->getOrCreateSettingsByProfile(
            $this->profileProvider->getCurrentProfile()
        );
    }
}
