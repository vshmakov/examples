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
private $sR;

    public function __construct(RegistryInterface $registry, ExampleRepository $exR, UserLoader $ul, SessionRepository $sR)
    {
        parent::__construct($registry, Attempt::class);
$this->exR=$exR;
$this->ul=$ul;
$this->sR=$sR;
    }

public function findLastByCurrentUser() {
$ul=$this->ul;
$w=(!$ul->isGuest()) ? "s.user = :u" : "a.session = :s";
$q=$this->q("select a from App:Attempt a
join a.session s
where $w
order by a.addTime desc")
->setMaxResults(1);
(!$ul->isGuest()) ? $q->setParameter("u", $ul->getUser()) : $q->setParameter("s", $this->sR->findOneByCurrentUserOrGetNew());
return $q->getOneOrNullResult();
}

public function getTitle($att) {
return "Попытка №".$this->getNumber($att);
}

public function getNumber($att) {
return $this->v(
$this->q("select count(a) from App:Attempt a
join a.session s
where s.user = :u and a.addTime <= :dt
")->setParameters(["u"=>$this->ul->getUser(), "dt"=>$att->getAddTime()])
);
}

public function getFinishTime($att) {
return $this->dt($this->v(
$this->q("select e.answerTime from App:Attempt a
join a.examples e
where a = :att and e.answerTime is not null
order by e.answerTime desc
")->setParameter("att", $att)
)) ?? $att->getAddTime();
}

public function getSolvedExamplesCount($att) {
return $this->v(
$this->q("select count(e) from App:Attempt a
join a.examples e
where e.answer != false and e.answer is not null and a = :a
")->setParameter("a", $att)
);
}

public function getErrorsCount($att) {
return $this->exR->count([
"attempt"=>$att,
"isRight"=>false,
]);
}

public function getRating($att) {
return 5;
}

public function findAllByCurrentUser() {
return $this->q("select a from App:Attempt a
join a.session s
join s.user u
where u = :u")
->setParameter("u", $this->ul->getUser())
->getResult();
}

public function getNewByCurrentUser() {
$att=(new Attempt())
->setSession($this->sR->findOneByCurrentUserOrGetNew());
$em=$this->em();
$em->persist($att);
$em->flush();
return $att;
}

}
