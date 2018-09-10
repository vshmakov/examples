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
    private $exR;
    private $attR;
    private $r;

    public function __construct(ExampleRepository $exR, AttemptRepository $attR, Router $r)
    {
        $this->exR = $exR;
        $this->attR = $attR;
        $this->r = $r;
    }

    public function getFunctions()
    {
        $fs = [];

        foreach ((get_class_methods($this)) as $f) {
            $f = trim($f);

            if ((preg_match('#^process#', $f))) {
                $fs[] = new TwigFunction($f, [$this, $f]);
            }
        }

        return $fs;
    }

    public function processExamples($examples)
    {
        $d = [];

        foreach ($examples as $ex) {
            $ex->setEr($this->exR);
            $att = $ex->getAttempt()->setER($this->attR);
            $d[] = [
                $ex->getUserNumber(),
                "$ex",
                $ex->isAnswered() ? $ex->getAnswer() : '-',
                !$ex->isAnswered() ? '-' : ($ex->isRight() ? 'Да' : 'Нет'),
                $ex->isAnswered() ? $ex->getSolvingTime()->getTimestamp() : '-',
                $ex->getAddTime().'',
                $this->r->link('attempt_show', ['id' => $att->getId()], $att->getTitle()),
            ];
        }

        return $d;
    }

    public function processIps($ips)
    {
        return $this->prepareData($ips, function ($pa) {
            return [
                $pa('id'),
                $pa('ip'),
                $pa('country'),
                $pa('region'),
                $pa('city'),
                $pa('continent'),
                $pa('addTime.dbFormat'),
                $this->r->link('ip_show', ['id' => $pa('id')], 'show')
                .$this->r->link('ip_edit', ['id' => $pa('id')], 'edit'),
            ];
        });
    }

    private function prepareData(array $entityList, callable $callback)
    {
        $data = [];

        foreach ($entityList as $entity) {
            $propertyAccessor = $this->getPropertyAccessor($entity);
            $data[] = $callback($propertyAccessor);
        }

        return $data;
    }

    private function getPropertyAccessor($entity)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        return function ($property) use ($entity, $propertyAccessor) {
            return $propertyAccessor->getValue($entity, $property) ?: '-';
        };
    }
}
