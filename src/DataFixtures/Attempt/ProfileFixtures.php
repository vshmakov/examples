<?php

namespace App\DataFixtures\Attempt;

use App\DataFixtures\UserFixtures;
use App\Entity\Profile;
use App\Object\ObjectAccessor;
use App\Profile\NormalizerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

final class ProfileFixtures extends Fixture implements DependentFixtureInterface
{
    /** @var NormalizerInterface */
    private $normalizer;

    public const GUEST_PROFILE_DESCRIPTION = 'Тестовый профиль';

    public const GUEST_PROFILE = [
        'description' => self::GUEST_PROFILE_DESCRIPTION,
        'isPublic' => true,
        'examplesCount' => 5,
    ];

    private const PUBLIC_PROFILES = [
        [
            'description' => 'Сложение в пределах 50',
            'addFMin' => 10,
            'addFMax' => 40,
            'addSMin' => 10,
            'addSMax' => 40,
            'addMin' => 20,
            'addMax' => 50,
            'addPerc' => 100,
            'subPerc' => 0,
            'multPerc' => 0,
            'divPerc' => 0,
        ],
    ];

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadGuestProfiles($manager);
        $this->loadPublicProfiles($manager);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }

    private function loadGuestProfiles(ObjectManager $manager): void
    {
        $profile = ObjectAccessor::initialize(Profile::class, self::GUEST_PROFILE);
        $guest = $this->getReference(UserFixtures::GUEST_USER_REFERENCE);
        $guest->setProfile($profile);
        $profile->setAuthor($guest);
        $manager->persist($profile);
    }

    private function loadPublicProfiles(ObjectManager $manager): void
    {
        foreach (self::PUBLIC_PROFILES as $profileData) {
            $profile = ObjectAccessor::initialize(Profile::class, $profileData);
            ObjectAccessor::setValues($profile, [
                'author' => $this->getReference(UserFixtures::ADMIN_USER_REFERENCE),
                'isPublic' => true,
            ]);

            $this->normalizer->normalize($profile);
            $manager->persist($profile);
        }
    }
}
