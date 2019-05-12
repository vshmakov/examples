<?php

namespace App\DataFixtures\Attempt;

use App\Attempt\AttemptFactoryInterface;
use App\Attempt\AttemptResultProviderInterface;
use App\DataFixtures\TaskFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Attempt;
use App\Entity\Example;
use App\Entity\Task;
use App\Object\ObjectAccessor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

final class AttemptFixtures extends Fixture implements DependentFixtureInterface
{
    /** @var AttemptFactoryInterface */
    private $attemptFactory;

    /** @var AttemptResultProviderInterface */
    private $attemptResultProvider;

    public function __construct(AttemptFactoryInterface $attemptCreator, AttemptResultProviderInterface $attemptResultProvider)
    {
        $this->attemptFactory = $attemptCreator;
        $this->attemptResultProvider = $attemptResultProvider;
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProfileFixtures::class,
            TaskFixtures::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $this->loadGuestAttempts($manager);
        $this->loadTaskAttempts($manager);
        $manager->flush();
    }

    private function loadGuestAttempts(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; ++$i) {
            $attempt = $this->attemptFactory->createCurrentUserAttempt();
            $this->solveAttempt($attempt, $manager);
        }
    }

    private function solveAttempt(Attempt $attempt, ObjectManager $manager): void
    {
        $manager->persist($attempt);

        while (!$attempt->isDone()) {
            $isWrongExample = 1 === ($attempt->getExamples()->count() + 1) % 3;
            $this->addExample($attempt, !$isWrongExample, $manager);
            $manager->flush();
            $this->attemptResultProvider->updateAttemptResult($attempt);
        }
    }

    private function addExample(Attempt $attempt, bool $isRight, ObjectManager $manager): void
    {
        /** @var Example $example */
        $example = ObjectAccessor::initialize(Example::class, [
            'first' => 2,
            'sign' => 1,
            'second' => 3,
            'answer' => $isRight ? 5 : 6,
            'isRight' => $isRight,
            'attempt' => $attempt,
        ]);
        $manager->persist($example);
        $attempt->addExample($example);
    }

    private function loadTaskAttempts(ObjectManager $manager): void
    {
        /** @var Task $task */
        $task = $this->getReference(TaskFixtures::TASK_REFERENCE);

        for ($i = 1; $i < $task->getTimesCount(); ++$i) {
            $attempt = $this->attemptFactory->createUserSolvesTaskAttempt($task, $this->getReference(UserFixtures::STUDENT_USER_REFERENCE));
            $this->solveAttempt($attempt, $manager);
        }
    }
}
