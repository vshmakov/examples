<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Object\ObjectAccessor;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    public const GUEST_USER_REFERENCE = 'GUEST_USER_REFERENCE';
    public const ADMIN_USER_REFERENCE = 'ADMIN_USER_REFERENCE';
    public const VADIM_USER_REFERENCE = 'VADIM_USER_REFERENCE';

    public const GUEST_USERNAME = UserRepository::GUEST_LOGIN;

    private const GUEST_USER = [
        'username' => self::GUEST_USERNAME,
        'roles' => ['ROLE_GUEST'],
    ];

    private const ADMIN_USER = [
        'username' => 'admin',
        'email' => 'admin@exmasters.ru',
        'plainPassword' => 123,
        'roles' => ['ROLE_ADMIN'],
    ];

    private const VADIM_USER = [
        'username' => 'vadim',
        'email' => 'vadim@exmasters.ru',
        'plainPassword' => 123,
    ];

    /** @var UserPasswordEncoderInterface */
    private $userPasswordEncoder;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadGuest($manager);
        $this->loadUsers($manager);
        $manager->flush();
    }

    private function loadGuest(ObjectManager $manager): void
    {
        $guest = ObjectAccessor::initialize(User::class, self::GUEST_USER);
        $this->addReference(self::GUEST_USER_REFERENCE, $guest);
        $manager->persist($guest);
    }

    private function loadUsers(ObjectManager $manager): void
    {
        foreach ([self::ADMIN_USER_REFERENCE => self::ADMIN_USER, self::VADIM_USER_REFERENCE => self::VADIM_USER] as $reference => $userData) {
            $user = ObjectAccessor::initialize(User::class, $userData);
            ObjectAccessor::setValues($user, [
                'enabled' => true,
                'password' => $this->userPasswordEncoder->encodePassword($user, $user->getPlainPassword()),
            ]);

            $this->addReference($reference, $user);
            $manager->persist($user);
        }
    }
}
