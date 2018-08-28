<?php

namespace App\Repository;

use App\Service\SessionMarker;
use App\Service\UserLoader;
use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SessionRepository extends ServiceEntityRepository
{
    use BaseTrait;
    private $ul;
    private $curUser;
    private $sm;

    public function __construct(RegistryInterface $registry, UserLoader $ul, SessionMarker $sm)
    {
        parent::__construct($registry, Session::class);
        $this->ul = $ul;
        $this->curUser = $ul->getUser();
        $this->sm = $sm;
    }

    public function findOneByCurrentUser()
    {
        return $this->findOneByUser($this->curUser);
    }

    public function findOneByUser($u)
    {
        $sid = $this->sm->getKey();

        return $this->findOneByUserAndSid($u, $sid);
    }

    public function findOneByCurrentUserOrGetNew()
    {
        return $this->findOneByUserOrGetNew($this->curUser);
    }

    public function findOneByUserOrGetNew($u)
    {
        return  $this->findOneByUser($u) ?? $this->getNewByUserAndSid($u, $this->sm->getKey());
    }

    public function findOneByUserAndSid($u, $sid)
    {
        $p = ['user' => $u];

        if ($u === $this->ul->getGuest()) {
            $p += ['sid' => $sid];
        }

        return $this->findOneBy($p);
    }

    private function getNewByUserAndSid($u, $sid)
    {
        if ($s = $this->findOneByUserAndSid($u, $sid)) {
            return $s;
        }

        $s = (new Session())
->setUser($u)
->setSid(($this->ul->isGuest()) ? $sid : '');
        $em = $this->em();
        $em->persist($s);
        $em->flush();

        return $s;
    }

    public function clearSessions($dt)
    {
        $s = $this->q('select s from App:Session s
left join s.attempts a
where a.id is null and s.lastTime < :dt')
->setParameter('dt', $dt)
->getResult();
        $em = $this->em();

        foreach ($s as $i) {
            $this->remove($i);
        }

        $em->flush();
    }

    public function remove($s)
    {
        $em = $this->em();

        foreach ($s->getVisits() as $v) {
            $s->removeVisit($v);
            $em->remove($v);
        }

        $em->remove($s);
    }
}
