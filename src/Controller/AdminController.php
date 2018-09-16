<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Repository\IpRepository;

class AdminController extends Controller
{
    use BaseTrait;

    /**
     * @Route("/admin", name="admin_index")
     */
    public function index()
    {
        $entityManager = $this->getEntityManager();

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
where u.addTime > :dt',
        ];

        foreach ($queries as $key => $query) {
            foreach ([1, 2, 3, 7, 14, 30, 60, 90] as $days) {
                $statistic[$days][$key] = IpRepository::getValueByQuery(
                    $entityManager->createQuery($query)
                        ->setParameter('dt', \DT::createBySubDays($days))
                );
            }
        }

        return $this->render('admin/index.html.twig', [
            'd' => $statistic,
        ]);
    }
}
