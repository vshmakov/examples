<?php

namespace App\Repository;

use App\Service\UserLoader;
use App\Entity\Attempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AttemptRepository extends ServiceEntityRepository
{
use BaseTrait;
private $exR;
private $ul;

    public function __construct(RegistryInterface $registry, ExampleRepository $exR, UserLoader $ul)
    {
        parent::__construct($registry, Attempt::class);
$this->exR=$exR;
$this->ul=$ul;
    }

public function getTitleByAttempt($att) {
return "Попытка №".$this->getNumberByAttempt($att);
}

public function getNumberByAttempt($att) {

}

public function getFinishTimeByAttempt($att) {

}

public function getSolvedExamplesCountByAttempt($att) {
}

public function getErrorsCountByAttempt($att) {
return $this->exR->count([
"attempt"=>$att,
"isRight"=>false,
]);
}

public function getRatingByAttempt($att) {

}

public function findAllByCurrentUser() {
return $this->q("select a from App:Attempt a
join a.session s
join s.user u
where u = :u")
->setParameter("u", $this->ul->getUser())
->getResult();
}
}
