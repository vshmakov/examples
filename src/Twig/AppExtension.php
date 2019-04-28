<?php

namespace App\Twig;

use App\Object\ObjectAccessor;
use App\Parameter\ChooseInterface;
use App\Parameter\Container\ParametersContainerInterface;
use App\Parameter\Environment\AppEnv;
use App\Parameter\StringInterface;
use App\Repository\AttemptRepository;
use App\Repository\HomeworkRepository;
use App\Repository\UserRepository;
use App\Security\User\CurrentUserProviderInterface;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class AppExtension extends AbstractExtension implements \Twig_Extension_GlobalsInterface
{
    use BaseTrait;
    private $userLoader;
    private $globals;
    private $entityManager;
    private $attemptRepository;
    private $taskRepository;
    private $userRepository;

    /** @var ParametersContainerInterface */
    private $javascriptParametersContainer;

    /** @var ChooseInterface */
    private $appEnv;

    public function __construct(
        CurrentUserProviderInterface $currentUserProvider,
        AttemptRepository $attemptRepository,
        HomeworkRepository $taskRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        ParametersContainerInterface $javascriptParametersContainer,
        StringInterface $appName,
        ChooseInterface $appEnv
    ) {
        $this->appEnv = $appEnv;
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;
        $this->userLoader = $currentUserProvider;
        $this->javascriptParametersContainer = $javascriptParametersContainer;

        $hasActualAttempt = (bool) $attemptRepository->getLastAttempt();
        $user = $currentUserProvider->getCurrentUserOrGuest();
        $user->setEntityRepository($userRepository);
        $this->globals = [
            'user' => $user,
            'hasActualAttempt' => $hasActualAttempt,
            'PRICE' => PRICE,
            'app_name' => $appName->toString(),
            'isGuest' => $currentUserProvider->isGuest($user),
            'FEEDBACK_EMAIL' => 'post@exmasters.ru',
        ];
    }

    public function getGlobals()
    {
        return $this->globals + [
                'javascriptParameters' => $this->javascriptParametersContainer->getParameters() + [
                        'isDevelopmentEnvironment' => $this->appEnv->is(AppEnv::DEV),
                    ],
            ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('toLabelString', [$this, 'toLabelStringFilter']),
            new TwigFilter('toJavascriptString', [$this, 'toJavascriptStringFilter']),
        ];
    }

    public function toJavascriptStringFilter(string $string): string
    {
        $lines = [];

        foreach (explode("\n", $string) as $line) {
            $trimmedLine = trim($line);

            if ('' !== $trimmedLine) {
                $lines[] = $trimmedLine;
            }
        }

        return implode('\n', $lines);
    }

    public function getFunctions()
    {
        return $this->prepareFunctions([
            'getJavascriptParameters',
            'dt',
            'sortByCreationTime',
            'sortByDateTime',
        ]);
    }

    public function getJavascriptParameters(): array
    {
        return $this->javascriptParametersContainer->getParameters();
    }

    public function toLabelStringFilter(string $property): string
    {
        $snakeCasedProperty = Inflector::tableize($property);

        return ucfirst(
            str_replace('_', ' ', $snakeCasedProperty)
        );
    }

    public function dt(string $staticMethod, ...$parameters)
    {
        return \call_user_func_array(sprintf(
            '\DT::%s',
            $staticMethod
        ), $parameters);
    }

    public function sortByCreationTime($entityList)
    {
        $this->sortByDateTime($entityList, 'addTime');

        return $entityList;
    }

    public function sortByDateTime(array $entityList, string $dateTimeProperty): array
    {
        usort($entityList, function ($entity1, $entity2) use ($dateTimeProperty): int {
            $time1 = ObjectAccessor::getNullableTraversedValue($entity1, "$dateTimeProperty.timestamp");
            $time2 = ObjectAccessor::getNullableTraversedValue($entity2, "$dateTimeProperty.timestamp");

            if ($time1 === $time2) {
                return 0;
            }

            if (null === $time1) {
                return -1;
            }

            if (null === $time2) {
                return 1;
            }

            return $time1 > $time2 ? 1 : -1;
        });

        return $entityList;
    }
}
