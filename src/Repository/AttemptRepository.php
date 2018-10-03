<?php

namespace App\Repository;

use Symfony\Component\PropertyAccess\PropertyAccess;
use App\Service\ExampleManager;
use App\Service\UserLoader;
use App\Service\AuthChecker;
use App\Entity\Attempt;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AttemptRepository extends ServiceEntityRepository
{
    use BaseTrait;
    private $exampleRepository;
    private $userLoader;
    private $sessionRepository;
    private $userRepository;
    private $authChecker;

    public function __construct(RegistryInterface $registry, ExampleRepository $exampleRepository, UserLoader $userLoader, SessionRepository $sessionRepository, UserRepository $userRepository, AuthChecker $authChecker)
    {
        parent::__construct($registry, Attempt::class);
        $this->exampleRepository = $exampleRepository;
        $this->userLoader = $userLoader;
        $this->sessionRepository = $sessionRepository;
        $this->userRepository = $userRepository;
        $this->authChecker = $authChecker;
    }

    public function findLastActualByCurrentUser()
    {
        $attempt = $this->findLastByCurrentUser();

        return $this->authChecker->isGranted('SOLVE', $attempt) ? $attempt : null;
    }

    public function findLastByCurrentUser()
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

    public function getTitle(Attempt $attempt)
    {
        return 'Попытка №'.$this->getNumber($attempt);
    }

    public function getNumber(Attempt $attempt)
    {
        return $this->getValue(
            $this->createQuery('select count(a) from App:Attempt a
join a.session s
where s.user = :u and a.addTime <= :dt
')->setParameters(['u' => $attempt->GetSession()->GetUser(), 'dt' => $attempt->getAddTime()])
        );
    }

    public function getFinishTime(Attempt $attempt)
    {
        return $this->dt($this->getValue(
            $this->createQuery('select e.answerTime from App:Attempt a
join a.examples e
where a = :att and e.answerTime is not null
order by e.answerTime desc
')->setParameter('att', $attempt)
        )) ?: $attempt->getAddTime();
    }

    public function getSolvedExamplesCount(Attempt $attempt)
    {
        return $attempt->getSettings()->isDemanding() ? $this->getValue(
            $this->createQuery('select count(e) from App:Attempt a
join a.examples e
where e.isRight = true and a = :a
')->setParameters(['a' => $attempt])
        ) : $this->getAnsweredExamplesCount($attempt);
    }

    public function getAnsweredExamplesCount(Attempt $attempt)
    {
        return $this->getValue(
            $this->createQuery('select count(e) from App:Attempt a
join a.examples e
where e.answer is not null and a = :a
')->setParameters(['a' => $attempt])
        );
    }

    public function getErrorsCount(Attempt $attempt)
    {
        return $this->exampleRepository->count([
            'attempt' => $attempt,
            'isRight' => false,
        ]);
    }

    public function getRating(Attempt $attempt)
    {
        return ExampleManager::rating($attempt->getExamplesCount(), $this->getRongExamplesCount($attempt));
    }

    public function countByCurrentUser()
    {
        return $this->getValue(
            $this->createQuery('select count(a) from App:Attempt a
join a.session s
join s.user u
where u = :u')
                ->setParameter('u', $this->userLoader->getUser())
        );
    }

    public function findAllByCurrentUser()
    {
        return $this->createQuery('select a from App:Attempt a
join a.session s
join s.user u
where u = :u')
            ->setParameter('u', $this->userLoader->getUser())
            ->getResult();
    }

    public function getNewByCurrentUser()
    {
        $user = $this->userLoader->getUser()
            ->setEntityRepository($this->userRepository);
        $attempt = (new Attempt())
            ->setSession($this->sessionRepository->findOneByCurrentUserOrGetNew())
            ->setSettings($user->getCurrentProfile()->getInstance());

        $entityManager = $this->getEntityManager();
        $entityManager->persist($attempt);
        $entityManager->flush();

        return $attempt;
    }

    public function hasPreviousExample(Attempt $attempt)
    {
        return (bool) $this->exampleRepository->findLastByAttempt($attempt);
    }

    public function getData(Attempt $attempt)
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

    public function getRemainedExamplesCount(Attempt $attempt)
    {
        $count = $attempt->getSettings()->getExamplesCount() - $this->getSolvedExamplesCount($attempt);

        return $count > 0 ? $count : 0;
    }

    public function getRemainedTime(Attempt $attempt)
    {
        $remainedTime = $attempt->getLimitTime()->getTimestamp() - time();

        return $remainedTime > 0 ? $remainedTime : 0;
    }

    public function getAllData(Attempt $attempt)
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

    public function getSolvedTime(Attempt $attempt)
    {
        return $this->dts(
            $this->getFinishTime($attempt)->getTimestamp() - $attempt->getAddTime()->getTimestamp()
        );
    }

    public function getAverSolveTime(Attempt $attempt)
    {
        $count = $this->getSolvedExamplesCount($attempt);

        return $this->dts(
            $count ? round($this->getSolvedTime($attempt)->getTimestamp() / $count) : 0
        );
    }

    public function getRongExamplesCount(Attempt $attempt)
    {
        return $this->getErrorsCount($attempt) + $attempt->getExamplesCount() - $this->getSolvedExamplesCount($attempt);
    }

    public function findByUser(User $user)
    {
        return $this->createQuery('select a from App:Attempt a
join a.session s
join s.user u
where u = ?1')
            ->setParameter(1, $user)
            ->getResult();
    }
}
