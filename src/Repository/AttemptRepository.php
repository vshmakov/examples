<?php

namespace App\Repository;

use App\Entity\Attempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AttemptRepository extends ServiceEntityRepository
{
use BaseTrait;

private $exR;

    public function __construct(RegistryInterface $registry, ExampleRepository $exR)
    {
        parent::__construct($registry, Attempt::class);
$this->exR=$exR;
    }

public function getTitleByAttempt($att) {
return "Попытка №".$this->getNumberByAttempt($att);
}

public function getNumberByAttempt($att) {

}

public function getFinishTimeByAttempt($att) {

}

public function getSolvedExamplesCountByAttempt($att) {
$this->v("")
}

public function getErrorsCountByAttempt($att) {
return $this->exR->count([
"attempt"=>$att,
"isRight"=>false,
]);
}

public function getRatingByAttempt($att) {

}
}
