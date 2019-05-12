<?php

namespace App\DataFixtures;

use App\Attempt\Settings\SettingsProviderInterface;
use App\DataFixtures\Attempt\ProfileFixtures;
use App\Entity\Task;
use App\Object\ObjectAccessor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

final class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public const TASK_REFERENCE = 'TASK';

    /** @var SettingsProviderInterface */
    private $settingsProvider;

    public function __construct(SettingsProviderInterface $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProfileFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 2; ++$i) {
            /** @var Task $task */
            $task = ObjectAccessor::initialize(Task::class, [
                'author' => $this->getReference(UserFixtures::TEACHER_USER_REFERENCE),
                'settings' => $this->settingsProvider->getOrCreateSettingsByProfile($this->getReference(ProfileFixtures::ADDITION_PROFILE_REFERENCE)),
            ]);

            $manager->persist($task);
            $manager->flush();
        }

        $this->addReference(self::TASK_REFERENCE, $task);
    }
}
