<?php

namespace App\Twig;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Service\Router;
use App\Repository\AttemptRepository;
use App\Repository\ExampleRepository;

class ProcessersExtension extends AbstractExtension
{
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
        $functions = [];

        foreach ((get_class_methods($this)) as $method) {
            if ((preg_match('#^process#', $method))) {
                $functions[] = new TwigFunction($method, [$this, $method]);
            }
        }

        return $functions;
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
                $propertyAccessor('answer'),
                $propertyAccessor('isRight', false) ? 'Да' : 'Нет',
                $propertyAccessor('solvingTime.timestamp') ? : '-',
                $propertyAccessor('addTime') . '',
                $this->router->link('attempt_show', ['id' => $attempt->getId()], $attempt->getTitle()),
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
                $this->router->link('ip_show', ['id' => $pa('id')], 'show')
                    . $this->router->link('ip_edit', ['id' => $pa('id')], 'edit'),
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
                $propertyAccessor('addTime') . '',
                $propertyAccessor('finishTime') . '',
                sprintf('%s из %s (%s сек/пример)', $propertyAccessor('solvingTime.minSecFormat'), $propertyAccessor('maxTime.minSecFormat'), $propertyAccessor('averSolveTime.timestamp')),
                sprintf('%s из %s', $propertyAccessor('solvedExamplesCount'), $propertyAccessor('examplesCount')),
                sprintf('<span style="background: %s;">%s</span>', $color, $rating)
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
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        return function ($property, $default = '-') use ($entity, $propertyAccessor) {
            $value = $propertyAccessor->getValue($entity, $property);
            if (false === $default) {
                $defaultSign = $value;
            }

            return $value ? : $defaultSign;
        };
    }
}
