<?php

namespace App\Repository;

use App\Entity\Session;
use App\Entity\User;
use App\Service\SessionMarker;
use App\Service\UserLoader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SessionRepository extends ServiceEntityRepository
{
    use BaseTrait;
    private $userLoader;
    private $currentUser;
    private $sessionMarker;

    public function __construct(RegistryInterface $registry, UserLoader $userLoader, SessionMarker $sessionMarker)
    {
        parent::__construct($registry, Session::class);
        $this->userLoader = $userLoader;
        $this->currentUser = $userLoader->getUser();
        $this->sessionMarker = $sessionMarker;
    }

    public function findOneByCurrentUser(): ?Session
    {
        return $this->findOneByUser($this->currentUser);
    }

    public function findOneByUser(User $user): ?Session
    {
        $sid = $this->sessionMarker->getKey();

        return $this->findOneByUserAndSid($user, $sid);
    }

    public function findOneByCurrentUserOrGetNew(): Session
    {
        return $this->findOneByUserOrGetNew($this->currentUser);
    }

    public function findOneByUserOrGetNew(User $user): Session
    {
        return $this->findOneByUser($user)
            ?? $this->getNewByUserAndSid($user, $this->sessionMarker->getKey());
    }

    public function findOneByUserAndSid(User $user, $sid): ?Session
    {
        $where = ['user' => $user];

        if ($user === $this->userLoader->getGuest()) {
            $where += ['sid' => $sid];
        }

        return $this->findOneBy($where);
    }

    private function getNewByUserAndSid(User $user, $sid): Session
    {
        if ($session = $this->findOneByUserAndSid($user, $sid)) {
            return $session;
        }

        $session = (new Session())
            ->setUser($user)
            ->setSid(($this->userLoader->isGuest()) ? $sid : '');

        $entityManager = $this->getEntityManager();
        $entityManager->persist($session);
        $entityManager->flush();

        return $session;
    }

    public function clearSessions(\DateTimeInterface $dt): int
    {
        $sessions = $this->createQuery('select s from App:Session s
left join s.attempts a
where a.id is null and s.lastTime < :dt')
            ->setParameter('dt', $dt)
            ->getResult();
        $removedSessionsCount = \count($sessions);
        $entityManager = $this->getEntityManager();

        foreach ($sessions as $session) {
            $this->remove($session);
        }

        return $removedSessionsCount;
    }

    public function remove(Session $session): void
    {
        $entityManager = $this->GetEntityManager();

        foreach ($session->getVisits() as $visit) {
            $session->removeVisit($visit);
            $entityManager->remove($visit);
        }

        $entityManager->remove($session);
        $entityManager->flush();
    }
}
