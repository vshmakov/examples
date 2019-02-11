<?php

namespace App\Repository;

use App\Entity\Session;
use App\Entity\User;
use App\Object\ObjectAccessor;
use App\Security\User\CurrentUserProviderInterface;
use App\Security\User\CurrentUserSessionProviderInterface;
use App\Service\SessionMarker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @deprecated
 */
final class SessionRepository extends ServiceEntityRepository implements CurrentUserSessionProviderInterface
{
    use BaseTrait;

    /** @var SessionMarker */
    private $sessionMarker;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(RegistryInterface $registry, SessionMarker $sessionMarker, CurrentUserProviderInterface $currentUserProvider, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($registry, Session::class);

        $this->sessionMarker = $sessionMarker;
        $this->currentUserProvider = $currentUserProvider;
        $this->authorizationChecker = $authorizationChecker;
    }

    private function getNewByUserAndSid(User $user, ?string $sid): Session
    {
        $session = ObjectAccessor::initialize(Session::class, [
            'user' => $user,
            'sid' => $sid,
        ]);

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

        foreach ($sessions as $session) {
            $this->remove($session);
        }

        return $removedSessionsCount;
    }

    private function remove(Session $session): void
    {
        $entityManager = $this->GetEntityManager();

        foreach ($session->getVisits() as $visit) {
            $session->removeVisit($visit);
            $entityManager->remove($visit);
        }

        $entityManager->remove($session);
        $entityManager->flush();
    }

    public function getCurrentUserSession(): ?Session
    {
        $where = ['user' => $this->currentUserProvider->getCurrentUserOrGuest()];

        if ($this->currentUserProvider->isGuest($user)) {
            $where += ['sid' => $this->sessionMarker->getKey()];
        }

        return $this->findOneBy($where);
    }

    public function getCurrentUserSessionOrNew(): Session
    {
        return $this->getCurrentUserSession() ?? $this->createSessionByCurrentUser();
    }

    private function createSessionByCurrentUser(): Session
    {
        return $this->getNewByUserAndSid(
            $this->currentUserProvider->getCurrentUserOrGuest(),
            $this->currentUserProvider->isCurrentUserGuest() ? $this->sessionMarker->getKey() : null
        );
    }

    public function isCurrentUserSession(Session $session): bool
    {
        return $session === $this->getCurrentUserSession();
    }
}
