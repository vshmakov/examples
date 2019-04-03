<?php

namespace App\Security\User;

use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Webmozart\Assert\Assert;

final class CurrentUserUpdatesUsernameSubscriber implements EventSubscriber
{
    /** @var callable */
    private $isCurrentUser;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SessionInterface */
    private $session;

    /** @var Request|null */
    private $request;

    private function __construct(callable $isCurrentUser, TokenStorageInterface $tokenStorage, SessionInterface $session, RequestStack $requestStack)
    {
        $this->isCurrentUser = $isCurrentUser;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
        $this->request = $requestStack->getMasterRequest();
    }

    public static function factory(ContainerInterface $container, TokenStorageInterface $tokenStorage, SessionInterface $session, RequestStack $requestStack): self
    {
        $isCurrentUser = function (User $user) use ($container): bool {
            return $container->get(CurrentUserProviderInterface::class)->isCurrentUser($user);
        };

        return new self($isCurrentUser, $tokenStorage, $session, $requestStack);
    }

    public function postUpdate(LifecycleEventArgs $eventArgs): void
    {
        if (null === $this->request) {
            return;
        }

        /** @var User $currentUser */
        $currentUser = $eventArgs->getObject();
        $isCurrentUser = $this->isCurrentUser;

        if (!$currentUser instanceof User or !$isCurrentUser($currentUser)) {
            return;
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $eventArgs->getObjectManager();
        Assert::isInstanceOf($entityManager, EntityManagerInterface::class);
        $unitOfWork = $entityManager->getUnitOfWork();
        $changes = $unitOfWork->getEntityChangeSet($currentUser);

        if (!\array_key_exists('username', $changes)) {
            return;
        }

        $token = new UsernamePasswordToken(
            $currentUser,
            null,
            'main',
            $currentUser->getRoles()
        );
        $this->tokenStorage->setToken($token);
        $this->session->set('_security_main', serialize($token));
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postUpdate,
        ];
    }
}
