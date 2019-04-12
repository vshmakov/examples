<?php

namespace App\Attempt\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Attempt\Example\EventSubscriber\ShowExamplesCollectionSubscriber;
use App\Entity\User;
use App\Security\User\CurrentUserProviderInterface;
use App\Security\Voter\UserVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

//TODO rename and move
final class FilterUserSubscriber implements EventSubscriberInterface
{
    use  RouteTrait;

    public const  FIELD = 'username';
    private const SUPPORTED_ROUTES = [
        ShowAttemptsCollectionSubscriber::ROUTE,
        ShowExamplesCollectionSubscriber::ROUTE,
    ];

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(CurrentUserProviderInterface $currentUserProvider, EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event): void
    {
        if (!$this->inRoutes(self::SUPPORTED_ROUTES, $event)) {
            return;
        }

        $query = $event->getRequest()->query;

        if (!$query->has(self::FIELD)) {
            throw new BadRequestHttpException(sprintf('You must specify %s parameter', self::FIELD));
        }

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneByUsername($query->get(self::FIELD));

        if (null === $user) {
            throw  new  NotFoundHttpException('User not found');
        }

        if (!$this->authorizationChecker->isGranted(UserVoter::SHOW_SOLVING_RESULTS, $user)) {
            throw new AccessDeniedHttpException('You can not show attempts of this user');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_SERIALIZE],
        ];
    }
}
