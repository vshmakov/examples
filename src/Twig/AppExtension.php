<?php

namespace App\Twig;

use App\Attempt\AttemptProviderInterface;
use App\Object\ObjectAccessor;
use App\Parameter\ChooseInterface;
use App\Parameter\Container\ParametersContainerInterface;
use App\Parameter\Environment\AppEnv;
use App\Parameter\StringInterface;
use App\Security\User\CurrentUserProviderInterface;
use Doctrine\Common\Inflector\Inflector;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class AppExtension extends AbstractExtension implements \Twig_Extension_GlobalsInterface
{
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var AttemptProviderInterface */
    private $attemptProvider;

    /** @var ParametersContainerInterface */
    private $javascriptParametersContainer;

    /** @var ChooseInterface */
    private $appEnv;

    /** @var StringInterface */
    private $appName;

    public function __construct(CurrentUserProviderInterface $currentUserProvider, AttemptProviderInterface $attemptProvider, ParametersContainerInterface $javascriptParametersContainer, ChooseInterface $appEnv, StringInterface $appName)
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->attemptProvider = $attemptProvider;
        $this->javascriptParametersContainer = $javascriptParametersContainer;
        $this->appEnv = $appEnv;
        $this->appName = $appName;
    }

    public function getGlobals()
    {
        return [
            'user' => $this->currentUserProvider->getCurrentUserOrGuest(),
            'hasActualAttempt' => null !== $this->attemptProvider->getLastAttempt(),
            'app_name' => $this->appName->toString(),
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
        return [
            new TwigFunction('dt', [$this, 'dt']),
            new TwigFunction('sortByCreationTime', [$this, 'sortByCreationTime']),
            new TwigFunction('sortByDateTime', [$this, 'sortByDateTime']),
        ];
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
