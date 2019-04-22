<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use  App\DateTime\DateTime as DT;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class IndexController extends Controller
{
    /**
     * @Route("/admin/", name="admin_index")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $statistic = [];
        $queries = [
            'ipC' => 'select count(i) from App:Ip i
where i.addTime > :dt',
            'visits' => 'select count(v) from App:Visit v
join v.session s
join s.ip i
where v.addTime > :dt and i.addTime > :dt',

            'attempts' => 'select count(a) from App:Attempt a
join a.session s
join s.ip i
where a.addTime > :dt and i.addTime > :dt',

            'users' => 'select count(u) from App:User u
where u.addTime > :dt and u.enabled = true',
        ];

        foreach ($queries as $key => $query) {
            foreach ([1, 3, 7, 14, 30, 60, 90, 180] as $days) {
                $statistic[$days][$key] = $entityManager
                    ->createQuery($query)
                    ->setParameter('dt', DT::createBySubDays($days))
                    ->getSingleScalarResult();
            }
        }

        return $this->render('admin/index.html.twig', [
            'd' => $statistic,
        ]);
    }
}
