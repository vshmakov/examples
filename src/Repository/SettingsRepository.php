<?php

namespace App\Repository;

use App\Entity\Settings;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Service\UserLoader;

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
        $settings = (new Settings());
        Settings::copySettings($profile, $settings);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($settings);
        dd($settings);
        $entityManager->flush();

        return $settings;
    }
}
