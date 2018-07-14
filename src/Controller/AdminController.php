<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Repository\{
IpRepository as IpR,
AttemptRepository as AttR,
};

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin_index")
     */
    public function index(IpR $ipR, AttR $attR)
    {
$d=[];
$d0=[
"ipC"=>"select count(i) from App:Ip i
where i.addTime > :dt",
"visits"=>"select count(v) from App:Visit v
join v.session s
join s.ip i
where v.addTime > :dt and i.addTime > :dt",
"attempts"=>"select count(a) from App:Attempt a
join a.session s
join s.ip i
where a.addTime > :dt and i.addTime > :dt",
"users"=>"select count(u) from App:User u
where u.addTime > :dt",
      ];

foreach ($d0 as $k=>$v) {
foreach ([1, 7, 14, 30, 60, 90] as $t) {
$d[$t][$k]=$ipR->v(
$ipR->q($v)->setParameter("dt", \DT::createBySubD($t))
);
}
}

        return $this->render('admin/index.html.twig', [
"d"=>$d
]);
    }
}
