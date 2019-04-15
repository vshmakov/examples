<?php

namespace App\DataFixtures\Attempt;

use App\Attempt\AttemptFactoryInterface;
use App\Attempt\AttemptResultProviderInterface;
use App\DataFixtures\PersistTrait;
use App\DataFixtures\UserFixtures;
use App\Entity\Attempt;
use App\Entity\Example;
use App\Object\ObjectAccessor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

final class AttemptFixtures extends Fixture implements DependentFixtureInterface
{
    use PersistTrait;

    /** @var AttemptFactoryInterface */
    private $attemptCreator;

    /** @var AttemptResultProviderInterface */
    private $attemptResultProvider;

    public function __construct(AttemptFactoryInterface $attemptCreator, AttemptResultProviderInterface $attemptResultProvider)
    {
        $this->attemptCreator = $attemptCreator;
        $this->attemptResultProvider = $attemptResultProvider;
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        for ($i = 1; $i <= 20; ++$i) {
            $attempt = $this->attemptCreator->createAttempt();
            $this->persist($attempt);

            while ($attempt->getExamples()->count() < $attempt->getSettings()->getExamplesCount()) {
                $isEvenExample = 0 === ($attempt->getExamples()->count() + 1) % 2;
                $this->addExample($attempt, !$isEvenExample);
            }

            $manager->flush();
            $this->attemptResultProvider->updateAttemptResult($attempt);
        }

        $manager->flush();
    }

    private function addExample(Attempt $attempt, bool $isRight): void
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
        $this->persist($example);
        $attempt->addExample($example);
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            ProfileFixtures::class,
        ];
    }
}
