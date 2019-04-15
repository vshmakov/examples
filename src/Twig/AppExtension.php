<?php

namespace App\Twig;

use App\Parameter\Container\ParametersContainerInterface;
use App\Parameter\StringInterface;
use App\Repository\AttemptRepository;
use App\Repository\HomeworkRepository;
use App\Repository\UserRepository;
use App\Security\User\CurrentUserProviderInterface;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class AppExtension extends AbstractExtension implements \Twig_Extension_GlobalsInterface
{
    use BaseTrait;
    private $userLoader;
    private $globals;
    private $entityManager;
    private $attemptRepository;
    private $taskRepository;
    private $userRepository;

    /** @var ParametersContainerInterface */
    private $javascriptParametersContainer;

    public function __construct(
        CurrentUserProviderInterface $currentUserProvider,
        AttemptRepository $attemptRepository,
        HomeworkRepository $taskRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        ParametersContainerInterface $javascriptParametersContainer,
        StringInterface $appName
    ) {
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;
        $this->userLoader = $currentUserProvider;
        $this->javascriptParametersContainer = $javascriptParametersContainer;

        $hasActualAttempt = (bool) $attemptRepository->getLastAttempt();
        $user = $currentUserProvider->getCurrentUserOrGuest();
        $user->setEntityRepository($userRepository);
        $this->globals = [
            'user' => $user,
            'hasActualAttempt' => $hasActualAttempt,
            'PRICE' => PRICE,
            'app_name' => $appName->toString(),
            'isGuest' => $currentUserProvider->isGuest($user),
            'FEEDBACK_EMAIL' => 'post@exmasters.ru',
        ];
    }

    public function getGlobals()
    {
        return $this->globals + [
                'javascriptParameters' => $this->javascriptParametersContainer->getParameters(),
            ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('toLabelString', [$this, 'toLabelStringFilter']),
        ];
    }

    public function getFunctions()
    {
        return $this->prepareFunctions([
            'getJavascriptParameters',
            'dt',
            'sortByCreationTime',
            'sortByDateTime',
        ]);
    }

    public function getJavascriptParameters(): array
    {
        return $this->javascriptParametersContainer->getParameters();
    }

    public function toLabelStringFilter(string $property): string
    {
        $snakeCasedProperty = Inflector::tableize($property);

        return ucfirst(
            str_replace('_', ' ', $snakeCasedProperty)
        );
    }

    public function dt(string $staticMethod, ...$parameters)
    {
        return \call_user_func_array(sprintf(
            '\DT::%s',
            $staticMethod
        ), $parameters);
    }

    public function creationTimeNumber($entity, array $entityList): int
    {
        $this->sortByAddTime($entityList);

        return array_search($entity, $entityList, true) + 1;
    }

    public function sortByCreationTime($entityList)
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

    public function sortByDateTime(array $entityList, string $dtProperty = 'addTime'): array
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();

        usort($entityList, function ($e1, $e2) use ($propertyAccessor, $dtProperty): int {
            $t1 = $propertyAccessor->getValue($e1, "$dtProperty.timestamp");
            $t2 = $propertyAccessor->getValue($e2, "$dtProperty.timestamp");

            if ($t1 === $t2) {
                return 0;
            }

            return $t1 > $t2 ? 1 : -1;
        });

        return $entityList;
    }

    public function sortTeachers($teachers)
    {
        $user = $this->userLoader->getUser();
        usort($teachers, function ($e1, $e2) use ($user) {
            if ($user->isUserTeacher($e1)) {
                return -1;
            }

            if ($user->isUserTeacher($e2)) {
                return 1;
            }

            $s1 = $e1->getStudents()->count();
            $s2 = $e2->getStudents()->count();

            if ($s1 !== $s2) {
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

    public function propertyFilter($objectOrArray, $property, $default = '-')
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableMagicCall()
            ->getPropertyAccessor();
        $value = $objectOrArray ? $propertyAccessor->getValue($objectOrArray, $property) : null;

        return false !== $default ? $value ?: $default : $value;
    }

    public function getActualHomeworksCount(): int
    {
        return $this->taskRepository->countActualHomeworksByCurrentUser();
    }
}
