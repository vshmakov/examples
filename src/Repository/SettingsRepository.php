<?php

namespace App\Repository;

use App\Entity\BaseProfile;
use App\Entity\Settings;
use App\Entity\User;
use App\Service\UserLoader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SettingsRepository extends ServiceEntityRepository
{
    use BaseTrait;
    private $userLoader;

    public function __construct(RegistryInterface $registry, UserLoader $userLoader)
    {
        parent::__construct($registry, Settings::class);
        $this->userLoader = $userLoader;
    }

    public function getNewByCurrentUser() : Settings
    {
        $currentUser = $this->userLoader->getUser()
            ->setEntityRepository($this->getEntityRepository(User::class));
        $profile = $currentUser->getCurrentProfile();

        return $this->findBySettingsDataOrNew($profile);
    }

    public function findBySettingsDataOrNew(BaseProfile $profile) : Settings
    {
        if ($settings = $this->findOneBy($profile->getSettings())) {
            return $settings;
        }

        $settings = new Settings();
        Settings::copySettings($profile, $settings);
        $entityManager = $this->getEntityManager();
        $entityManager->persist($settings);
        $entityManager->flush();

        return $settings;
    }
}
