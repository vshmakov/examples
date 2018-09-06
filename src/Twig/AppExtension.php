<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Service\UserLoader as UL;
use App\Repository\AttemptRepository as AttR;
use App\Repository\UserRepository as UR;
use   Doctrine\ORM\EntityManagerInterface as EM;
use   Psr\Container\ContainerInterface as Con;
use   Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface as ConP;

class AppExtension extends AbstractExtension implements \Twig_Extension_GlobalsInterface
{
    private $ul;
    private $gl = [];
    private $em;

    public function __construct(UL $ul, AttR $attR, UR $uR, EM $em, Con $con, ConP $conP)
    {
        $hasAtt = (bool) $attR->findLastActualByCurrentUser();

        $this->em = $em;
        $this->ul = $ul;
        $this->gl = [
            'user' => $u = $ul->getUser()->setER($uR),
            'hasActualAttempt' => $hasAtt,
            'PRICE' => PRICE,
            'app_name' => $con->getParameter('app_name'),
            'vk_app_id' => $con->getParameter('vk_app_id'),
            'isGuest' => $ul->isGuest(),
            'FEEDBACK_EMAIL' => 'post@exmasters.ru',
        ];
    }

    public function getGlobals()
    {
        return $this->gl;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('addTimeNumber', [$this, 'getAddTimeNumber']),
            new TwigFunction('sortByAddTime', [$this, 'sortByAddTime']),
            new TwigFunction('sortProfiles', [$this, 'sortProfiles']),
            new TwigFunction('sortTeachers', [$this, 'sortTeachers']),
            new TwigFunction('getIpInfo', '\App\Service\IpInformer::getInfoByIp'),
            new TwigFunction('fillIp', [$this, 'fillIp']),
        ];
    }

    public function getAddTimeNumber($v, array $ents)
    {
        $dt = $v->getAddTime();
        $n = count($ents);

        foreach ($ents as $e) {
            if ($dt->getTimestamp() < $e->getAddTime()->getTimestamp()) {
                --$n;
            }
        }

        return $n;
    }

    public function sortByAddTime($ents)
    {
        usort($ents, [$this, 'addTimeSorter']);

        return $ents;
    }

    public function sortProfiles($ps)
    {
        $cp = $this->ul->getUser()->getCurrentProfile();
        usort($ps, function ($e1, $e2) use ($cp) {
            if ($cp === $e1) {
                return  -1;
            }

            if ($cp === $e2) {
                return  1;
            }

            return $this->addTimeSorter($e1, $e2);
        });

        return $ps;
    }

    public function fillIp($ip)
    {
        if ($ip->getCity() && !$ip->getContinent()) {
            $ip->setIp($ip->getIp());
            $this->em->flush();
        }
    }

    private function addTimeSorter($e1, $e2)
    {
        $t1 = $e1->getAddTime()->getTimestamp();
        $t2 = $e2->getAddTime()->getTimestamp();

        if ($t1 == $t2) {
            return 0;
        }

        return $t1 > $t2 ? 1 : -1;
    }

    public function sortTeachers($ts)
    {
        $u = $this->ul->getUser();
        usort($ts, function ($e1, $e2) use ($u) {
            if ($u->isUserTeacher($e1)) {
                return -1;
            }

            if ($u->isUserTeacher($e2)) {
                return 1;
            }

            $s1 = $e1->getStudents()->count();
            $s2 = $e2->getStudents()->count();

            if ($s1 != $s2) {
                return $s1 > $s2 ? -1 : 1;
            }

            return $this->addTimeSorter($e1, $e2);
        });

        return $ts;
    }
}
