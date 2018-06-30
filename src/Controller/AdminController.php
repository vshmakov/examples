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
     * @Route("/admin", name="admin")
     */
    public function index(IpR $ipR, AttR $attR)
    {
$d=[
"ipC"=>$ipR->q("select i from App:Ip i
where i.addTime > :dt")
        ];

foreach ($d as $k=>$v) {
$d[$k]=$ipR->v(
$v->setParameter("dt", (new \DateTime)->sub(new \DateInterval("P1D")))
);
}

        return $this->render('admin/index.html.twig', $d);
    }
}
