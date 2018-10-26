<?php

namespace App\Repository;

use Symfony\Component\PropertyAccess\PropertyAccess;
use App\Service\ExampleManager;
use App\Service\UserLoader;
use App\Service\AuthChecker;
use App\Entity\Attempt;
use App\Entity\User;
use App\Entity\Session;
use App\Entity\Settings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\Task;
use App\Utils\Cache\LocalCache;

class AttemptRepository extends ServiceEntityRepository
{
    use BaseTrait;
    private $exampleRepository;
    private $userLoader;
    private $sessionRepository;
    private $userRepository;
    private $authChecker;
    private $localCache;

    public function __construct(RegistryInterface $registry, ExampleRepository $exampleRepository, UserLoader $userLoader, SessionRepository $sessionRepository, UserRepository $userRepository, AuthChecker $authChecker, LocalCache $localCache)
    {
        parent::__construct($registry, Attempt::class);
        $this->exampleRepository = $exampleRepository;
        $this->userLoader = $userLoader;
        $this->sessionRepository = $sessionRepository;
        $this->userRepository = $userRepository;
        $this->authChecker = $authChecker;
        $this->localCache = $localCache;
    }

    public function findLastActualByCurrentUser() : ? Attempt
    {
        $attempt = $this->findLastByCurrentUser();

        return $this->authChecker->isGranted('SOLVE', $attempt) ? $attempt : null;
    }

    public function findLastByCurrentUser() : ? Attempt
    {
        $userLoader = $this->userLoader;
        $where = !$userLoader->isGuest() ? 's.user = :u' : 'a.session = :s';
        $query = $this->createQuery("select a from App:Attempt a
join a.session s
where $where
order by a.addTime desc");
        $parameters = !$userLoader->isGuest() ? ['u' => $userLoader->getUser()] : ['s' => $this->sessionRepository->findOneByCurrentUserOrGetNew()];
        $query->setParameters($parameters);

        return $this->getValue($query);
    }

    public function getTitle(Attempt $attempt) : string
    {
        return 'Попытка №' . $this->getNumber($attempt);
    }

    public function getNumber(Attempt $attempt) : int
    {
        return $this->getValue(
            $this->createQuery('select count(a) from App:Attempt a
join a.session s
where s.user = :u and a.addTime <= :dt
')->setParameters(['u' => $attempt->GetSession()->GetUser(), 'dt' => $attempt->getAddTime()])
        );
    }

    public function getFinishTime(Attempt $attempt) : \DateTimeInterface
    {
        return $this->dt($this->getValue(
            $this->createQuery('select e.answerTime from App:Attempt a
join a.examples e
where a = :att and e.answerTime is not null
order by e.answerTime desc
')->setParameter('att', $attempt)
        )) ? : $attempt->getAddTime();
    }

    public function getSolvedExamplesCount(Attempt $attempt) : int
    {
        return $attempt->getSettings()->isDemanding() ? $this->getValue(
            $this->createQuery('select count(e) from App:Attempt a
join a.examples e
where e.isRight = true and a = :a
')->setParameters(['a' => $attempt])
        ) : $this->getAnsweredExamplesCount($attempt);
    }

    public function getAnsweredExamplesCount(Attempt $attempt) : int
    {
        return $this->getValue(
            $this->createQuery('select count(e) from App:Attempt a
join a.examples e
where e.answer is not null and a = :a
')->setParameters(['a' => $attempt])
        );
    }

    public function getErrorsCount(Attempt $attempt) : int
    {
        return $this->exampleRepository->count([
            'attempt' => $attempt,
            'isRight' => false,
        ]);
    }

    public function getRating(Attempt $attempt) : int
    {
        return ExampleManager::rating($attempt->getExamplesCount(), $this->getRongExamplesCount($attempt));
    }

    public function countByCurrentUser() : int
    {
        return $this->getValue(
            $this->createQuery('select count(a) from App:Attempt a
join a.session s
join s.user u
where u = :u')
                ->setParameter('u', $this->userLoader->getUser())
        );
    }

    public function findAllByCurrentUser() : array
    {
        return $this->createQuery('select a from App:Attempt a
join a.session s
join s.user u
where u = :u')
            ->setParameter('u', $this->userLoader->getUser())
            ->getResult();
    }

    public function getNewByCurrentUser() : Attempt
    {
        return $this->getNewByCurrentUserAndSettings($this->getEntityRepository(Settings::class)->getNewByCurrentUser());
    }

    public function getNewByCurrentUserAndSettings(Settings $settings) : Attempt
    {
        $attempt = (new Attempt())
            ->setSession($this->getEntityRepository(Session::class)->findOneByCurrentUserOrGetNew())
            ->setSettings($settings);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($attempt);
        $entityManager->flush();

        return $attempt;
    }

    public function hasPreviousExample(Attempt $attempt) : bool
    {
        return (bool)$this->exampleRepository->findLastByAttempt($attempt);
    }

    public function getData(Attempt $attempt) : array
    {
        $exampleRepository = $this->exampleRepository;

        if (!$example = $exampleRepository->findLastUnansweredByAttempt($attempt)) {
            return false;
        }
        $example->setEntityRepository($exampleRepository);
        $attempt->setEntityRepository($this);

        return [
            'ex' => [
                'num' => $example->getNumber(),
                'str' => "$example",
            ],
            'errors' => $attempt->getErrorsCount(),
            'exRem' => $attempt->getRemainedExamplesCount(),
            'limTime' => $attempt->getLimitTime()->getTimestamp(),
        ];
    }

    public function getRemainedExamplesCount(Attempt $attempt) : int
    {
        $count = $attempt->getSettings()->getExamplesCount() - $this->getSolvedExamplesCount($attempt);

        return $count > 0 ? $count : 0;
    }

    public function getRemainedTime(Attempt $attempt) : \DateTimeInterface
    {
        $remainedTime = $attempt->getLimitTime()->getTimestamp() - time();

        return $this->dts($remainedTime > 0 ? $remainedTime : 0);
    }

    public function getAllData(Attempt $attempt) : array
    {
        $data = $attempt->setEntityRepository($this)->getData();
        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableMagicCall()
            ->getPropertyAccessor();

        foreach (arr('title number finishTime solvedExamplesCount answeredExamplesCount errorsCount rating') as $property) {
            $data[$property] = $propertyAccessor->getValue($attempt, $property);
        }

        return $data;
    }

    public function getSolvedTime(Attempt $attempt) : \DateTimeInterface
    {
        return $this->dts(
            $this->getFinishTime($attempt)->getTimestamp() - $attempt->getAddTime()->getTimestamp()
        );
    }

    public function getAverSolveTime(Attempt $attempt) : \DateTimeInterface
    {
        $count = $this->getSolvedExamplesCount($attempt);

        return $this->dts(
            $count ? round($this->getSolvedTime($attempt)->getTimestamp() / $count) : 0
        );
    }

    public function getRongExamplesCount(Attempt $attempt) : int
    {
        return $this->getErrorsCount($attempt) + $attempt->getExamplesCount() - $this->getSolvedExamplesCount($attempt);
    }

    public function findByUser(User $user) : array
    {
        return $this->createQuery('select a from App:Attempt a
join a.session s
join s.user u
where u = ?1')
            ->setParameter(1, $user)
            ->getResult();
    }

    public function isDone(Attempt $attempt) : bool
    {
        return $this->getSolvedExamplesCount($attempt) == $attempt->getSettings()->getExamplesCount();
    }

    public function findByTaskAndUser(Task $task, User $user) : array
    {
        return $this->createQuery('select a from App:Attempt a
join a.session s
where a.task = :task and s.user = :user')
            ->setParameters(['task' => $task, 'user' => $user])
            ->getResult();
    }

    public function findLastOneByTaskAndUser(Task $task, User $user) : ? Attempt
    {
        return $this->getValue(
            $this->createQuery('select a from App:Attempt a
        join a.session s
        where a.task = :task and s.user = :user
        order by a.addTime desc')
                ->setParameters(['task' => $task, 'user' => $user])
        );
    }

    public function findDoneByTaskAndUser(Task $task, User $user) : array
    {
        $attempts = $this->findByTaskAndUser($task, $user);

        return array_filter($attempts, function (Attempt $attempt) : bool {
            return $this->isDone($attempt);
        });
    }

    public function getAverageRatingByTaskAndUser(Task $task, User $user) : ? float
    {
        $attempts = $this->findByTaskAndUser($task, $user);
        $attemptsCount = count($attempts);
        $ratingSumm = array_reduce($attempts, function (int $ratingSumm, Attempt $attempt) : int {
            return $ratingSumm + $this->getRating($attempt);
        }, 0);

        return $attemptsCount ? $ratingSumm / $attemptsCount : null;
    }

    public function getAverageRatingByTaskAndCurrentUser(Task $task) : ? float
    {
        return $this->getAverageRatingByTaskAndUser($task, $this->userLoader->getUser());
    }

    public function findByTaskAndCurrentUser(Task $task) : array
    {
        return $this->findByTaskAndUser($task, $this->userLoader->getUser());
    }
}
