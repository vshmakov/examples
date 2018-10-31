<?php

namespace App\Twig;

use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;
use App\Service\Router;
use App\Utils\PerformanceMeter;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Twig\Extension\AbstractExtension;

class ProcessersExtension extends AbstractExtension
{
    use BaseTrait;
    private $exampleRepository;
    private $attemptRepository;
    private $router;
    private $performanceMeter;

    public function __construct(ExampleRepository $exampleRepository, AttemptRepository $attemptRepository, Router $router, PerformanceMeter $performanceMeter)
    {
        $this->exampleRepository = $exampleRepository;
        $this->attemptRepository = $attemptRepository;
        $this->router = $router;
        $this->performanceMeter = $performanceMeter;
    }

    public function getFunctions()
    {
        return $this->prepareFunctions(
            array_filter(
                get_class_methods($this),
                function ($method) {
                    return (bool) preg_match('#^process#', $method);
                }
            )
        );
    }

    public function processVisits(array $visits): array
    {
        return $this->prepareData($visits, function ($propertyAccessor) {
            $this->performanceMeter->start('processers_extension.process_visits');
            $uri = $propertyAccessor('uri');

            $result = [
                $propertyAccessor('id'),
                $propertyAccessor('session.user.dumpName'),
                $propertyAccessor('routeName'),
                $propertyAccessor('method'),
                $propertyAccessor('statusCode'),
                $this->router->link($uri, $uri),
                $propertyAccessor('addTime.dbFormat'),
                $propertyAccessor('session.id'),
                $propertyAccessor('session.ip.country'),
                $propertyAccessor('session.ip.region'),
                $propertyAccessor('session.ip.city'),
                $this->router->linkToRoute('session_visits', ['id' => $propertyAccessor('session.id')], sprintf('Visits of session (%s)', $propertyAccessor('session.visits.count'))),
            ];

            $this->performanceMeter->finish('processers_extension.process_visits');

            return $result;
        });
    }

    public function processExamples(array $examples): array
    {
        $number = 0;

        return $this->prepareData($examples, function ($propertyAccessor, $example) use (&$number): array {
            $example->setEntityRepository($this->exampleRepository);
            $attempt = $example->getAttempt()
                ->setEntityRepository($this->attemptRepository);

            return [
                ++$number,
                "$example",
                $propertyAccessor('answer', false) ?? '-',
                $propertyAccessor('isRight', false) ? 'Да' : 'Нет',
                $propertyAccessor('solvingTime.timestamp') ?: '-',
                $propertyAccessor('addTime').'',
                $this->router->linkToRoute('attempt_show', ['id' => $attempt->getId()], $attempt->getTitle()),
                $this->router->linkToRoute('attempt_profile', ['id' => $attempt->getId()], $attempt->getSettings()->getDescription()),
            ];
        });
    }

    public function processIps(array $ips): array
    {
        return $this->prepareData($ips, function ($propertyAccessor) {
            return [
                $propertyAccessor('id'),
                $propertyAccessor('ip'),
                $propertyAccessor('country'),
                $propertyAccessor('region'),
                $propertyAccessor('city'),
                $propertyAccessor('continent'),
                $propertyAccessor('addTime.dbFormat'),
                $this->router->linkToRoute('ip_show', ['id' => $propertyAccessor('id')], 'show')
                    .$this->router->linkToRoute('ip_edit', ['id' => $propertyAccessor('id')], 'edit'),
            ];
        });
    }

    public function processAttempts(array $attempts): array
    {
        return $this->prepareData($attempts, function ($propertyAccessor, $attempt) {
            $attempt->setEntityRepository($this->attemptRepository);
            $rating = $propertyAccessor('rating');

            switch ($rating) {
                case 3:
                    $color = 'orange';

                    break;

                case 4:
                    $color = 'yellow';

                    break;

                case 5:
                    $color = 'green';

                    break;

                default:
                    $color = 'red';

                    break;
            }

            return [
                $this->router->linkToRoute('attempt_show', ['id' => $propertyAccessor('id')], $propertyAccessor('title')),
                $propertyAccessor('addTime').'',
                $propertyAccessor('finishTime').'',
                $this->router->linkToRoute('attempt_profile', ['id' => $propertyAccessor('id')], $propertyAccessor('settings.description')),
                $propertyAccessor('solvedExamplesCount', false) ? sprintf('%s из %s (%s сек/пример)', $propertyAccessor('solvedTime.minSecFormat'), $propertyAccessor('maxTime.minSecFormat'), $propertyAccessor('averSolveTime.timestamp')) : '-',
                sprintf('%s из %s', $propertyAccessor('solvedExamplesCount', false), $propertyAccessor('examplesCount', false)),
                $propertyAccessor('errorsCount', false),
                sprintf('<span style="background: %s;">%s</span>', $color, $rating),
            ];
        });
    }

    private function prepareData(array $entityList, callable $callback)
    {
        $this->performanceMeter->start('processers_extension.prepare_data');

        $data = [];

        foreach ($entityList as $key => $entity) {
            $propertyAccessor = $this->getPropertyAccessor($entity);
            $data[] = $callback($propertyAccessor, $entity, $key, $entityList);
        }

        $this->performanceMeter->finish('processers_extension.prepare_data');

        return $data;
    }

    private function getPropertyAccessor($entity)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableMagicCall()
            ->getPropertyAccessor();

        return function ($property, $defaultSign = '-') use ($entity, $propertyAccessor) {
            $value = $propertyAccessor->getValue($entity, $property);

            if (false === $defaultSign) {
                $defaultSign = $value;
            }

            return $value ?: $defaultSign;
        };
    }
}
