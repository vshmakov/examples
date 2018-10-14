<?php

namespace App\Twig;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Twig\Extension\AbstractExtension;
use App\Service\Router;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;

class ProcessersExtension extends AbstractExtension
{
    use BaseTrait;
    private $exampleRepository;
    private $attemptRepository;
    private $router;

    public function __construct(ExampleRepository $exampleRepository, AttemptRepository $attemptRepository, Router $router)
    {
        $this->exampleRepository = $exampleRepository;
        $this->attemptRepository = $attemptRepository;
        $this->router = $router;
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

    public function processExamples($examples)
    {
        return $this->prepareData($examples, function ($propertyAccessor, $example) {
            $example->setEntityRepository($this->exampleRepository);
            $attempt = $example->getAttempt()
                ->setEntityRepository($this->attemptRepository);

            return [
                $propertyAccessor('userNumber'),
                "$example",
                $propertyAccessor('answer', false) ?? '-',
                $propertyAccessor('isRight', false) ? 'Да' : 'Нет',
                $propertyAccessor('solvingTime.timestamp') ?: '-',
                $propertyAccessor('addTime').'',
                $this->router->link('attempt_show', ['id' => $attempt->getId()], $attempt->getTitle()),
                $this->router->link('attempt_profile', ['id' => $attempt->getId()], $attempt->getSettings()->getDescription()),
            ];
        });
    }

    public function processIps($ips)
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
                $this->router->link('ip_show', ['id' => $propertyAccessor('id')], 'show')
                    .$this->router->link('ip_edit', ['id' => $propertyAccessor('id')], 'edit'),
            ];
        });
    }

    public function processAttempts($attempts)
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
                $this->router->link('attempt_show', ['id' => $propertyAccessor('id')], $propertyAccessor('title')),
                $propertyAccessor('addTime').'',
                $propertyAccessor('finishTime').'',
                $propertyAccessor('solvedExamplesCount', false) ? sprintf('%s из %s (%s сек/пример)', $propertyAccessor('solvedTime.minSecFormat'), $propertyAccessor('maxTime.minSecFormat'), $propertyAccessor('averSolveTime.timestamp')) : '-',
                sprintf('%s из %s', $propertyAccessor('solvedExamplesCount', false), $propertyAccessor('examplesCount', false)),
                $propertyAccessor('errorsCount', false),
                sprintf('<span style="background: %s;">%s</span>', $color, $rating),
            ];
        });
    }

    private function prepareData(array $entityList, callable $callback)
    {
        $data = [];

        foreach ($entityList as $key => $entity) {
            $propertyAccessor = $this->getPropertyAccessor($entity);
            $data[] = $callback($propertyAccessor, $entity, $key, $entityList);
        }

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
