<?php

namespace App\Twig;

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
}
