<?php

namespace App\Twig;

use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Service\UserLoader;
use App\Repository\AttemptRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class AppExtension extends AbstractExtension implements \Twig_Extension_GlobalsInterface
{
    private $userLoader;
    private $globals = [];
    private $entityManager;
    private $attemptRepository;
    private $userRepository;

    public function __construct(UserLoader $userLoader, AttemptRepository $attemptRepository, UserRepository $userRepository, EntityManagerInterface $entityManager, ContainerInterface $container)
    {
        $hasActualAttempt = (bool)$attemptRepository->findLastActualByCurrentUser();
        $user = $userLoader->getUser()->setEntityRepository($userRepository);

        $this->entityManager = $entityManager;
        $this->userLoader = $userLoader;
        $this->globals = [
            'user' => $user,
            'hasActualAttempt' => $hasActualAttempt,
            'PRICE' => PRICE,
            'app_name' => $container->getParameter('app_name'),
            'isGuest' => $userLoader->isGuest(),
            'FEEDBACK_EMAIL' => 'post@exmasters.ru',
        ];
    }

    public function getGlobals()
    {
        return $this->globals;
    }

    public function getFunctions()
    {
        return $this->prepareFunctions([
            'addTimeNumber',
            'sortByAddTime',
            'sortProfiles',
            'sortTeachers',
            'sortStudents',
            'fillIp',
        ]);
    }

    public function getAddTimeNumber($entity, array $entityList)
    {
        $addTime = $entity->getAddTime();

        return array_reduce(
            $entityList,
            function ($number, $entity) use ($addTime) {
                return $addTime->getTimestamp() < $entity->getAddTime()->getTimestamp() ? --$number : $number;
            },
            count($entityList)
        );
    }

    public function sortByAddTime($entityList)
    {
        usort($entityList, [$this, 'addTimeSorter']);

        return $entityList;
    }

    public function sortProfiles($profiles)
    {
        $currentProfile = $this->userLoader->getUser()->getCurrentProfile();
        usort($profiles, function ($e1, $e2) use ($currentProfile) {
            if ($currentProfile === $e1) {
                return -1;
            }

            if ($currentProfile === $e2) {
                return 1;
            }

            return $this->addTimeSorter($e1, $e2);
        });

        return $profiles;
    }

    public function fillIp($ip)
    {
        if ($ip->getCity() && !$ip->getContinent()) {
            $ip->setIp($ip->getIp());
            $this->entityManager->flush();
        }
    }

    private function addTimeSorter($e1, $e2)
    {
        return addTimeSorter($e1, $e2);
    }

    public function sortTeachers($teachers)
    {
        $user = $this->userLoader->getUser();
        usort($ts, function ($e1, $e2) use ($user) {
            if ($user->isUserTeacher($e1)) {
                return -1;
            }

            if ($user->isUserTeacher($e2)) {
                return 1;
            }

            $s1 = $e1->getStudents()->count();
            $s2 = $e2->getStudents()->count();

            if ($s1 != $s2) {
                return $s1 > $s2 ? -1 : 1;
            }

            return $this->addTimeSorter($e1, $e2);
        });

        return $teachers;
    }

    public function sortStudents($students)
    {
        usort($students, function ($e1, $e2) {
            $a1 = $e1->getAttempts()->last();
            $a2 = $e2->getAttempts()->last();

            if (!$a1) {
                return 1;
            }

            if (!$a2) {
                return -1;
            }

            return -1 * addTimeSorter($a1, $a2);
        });

        return $students;
    }
}
