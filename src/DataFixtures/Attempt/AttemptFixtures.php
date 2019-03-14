<?php

namespace App\DataFixtures\Attempt;

use App\DataFixtures\UserFixtures;
use App\Settings\SettingsProviderInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

final class AttemptFixtures extends Fixture implements DependentFixtureInterface
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            ProfileFixtures::class,
        ];
    }
}
