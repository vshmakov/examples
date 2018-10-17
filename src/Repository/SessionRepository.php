<?php

namespace App\Repository;

use App\Service\SessionMarker;
use App\Service\UserLoader;
use App\Entity\Session;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Utils\Cache\LocalCache;

class SessionRepository extends ServiceEntityRepository
{
    use BaseTrait;
    private $userLoader;
    private $currentUser;
    private $sessionMarker;
    private $localCache;

    public function __construct(RegistryInterface $registry, UserLoader $userLoader, SessionMarker $sessionMarker, LocalCache $localCache)
    {
        parent::__construct($registry, Session::class);
        $this->userLoader = $userLoader;
        $this->currentUser = $userLoader->getUser();
        $this->sessionMarker = $sessionMarker;
        $this->localCache = $localCache;
    }

    public function findOneByCurrentUser()
    {
        return $this->findOneByUser($this->currentUser);
    }

    public function findOneByUser(User $user)
    {
        $sid = $this->sessionMarker->getKey();

        $this->localCache->get(['users[%s, sid=%s].session', $user, $sid], function () use ($user, $sid) {
            return $this->findOneByUserAndSid($user, $sid);
        });
    }

    public function findOneByCurrentUserOrGetNew()
    {
        return $this->findOneByUserOrGetNew($this->currentUser);
    }

    public function findOneByUserOrGetNew(User $user)
    {
        return $this->findOneByUser($user)
            ?? $this->getNewByUserAndSid($user, $this->sessionMarker->getKey());
    }

    public function findOneByUserAndSid(User $user, $sid)
    {
        $where = ['user' => $user];

        if ($user === $this->userLoader->getGuest()) {
            $where += ['sid' => $sid];
        }

        return $this->findOneBy($where);
    }

    private function getNewByUserAndSid(User $user, $sid)
    {
        if ($session = $this->findOneByUserAndSid($user, $sid)) {
            return $session;
        }

        $sid = $this->userLoader->isGuest() ? $sid : '';
        $session = (new Session())
            ->setUser($user)
            ->setSid($sid);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($session);
        $entityManager->flush();

        return $this->localCache->set(['users[%s, sid=%s].session', $user, $sid], $session);
    }

    public function clearSessions(\DateTimeInterface $dt)
    {
        $sessions = $this->createQuery('select s from App:Session s
left join s.attempts a
where a.id is null and s.lastTime < :dt')
            ->setParameter('dt', $dt)
            ->getResult();
        $entityManager = $this->getEntityManager();

        foreach ($sessions as $session) {
            $this->remove($session);
        }

        return count($sessions);
    }

    public function remove(Session $session)
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
