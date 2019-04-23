<?php

namespace App\Controller;

use App\ApiPlatform\Attribute;
use App\ApiPlatform\Filter\Validation\FilterUserValidationSubscriber;
use App\ApiPlatform\Format;
use App\Attempt\EventSubscriber\ShowAttemptsCollectionSubscriber;
use App\Attempt\Example\EventSubscriber\ShowExamplesCollectionSubscriber;
use App\Controller\Traits\CurrentUserProviderTrait;
use App\Controller\Traits\JavascriptParametersTrait;
use App\Entity\User;
use App\Entity\User\Role;
use App\Security\Annotation as AppSecurity;
use App\Security\Voter\UserVoter;
use App\User\Teacher\Exception\RequiresTeacherAccessException;
use App\User\UserEvaluatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/student")
 * @IsGranted(Role::USER)
 * @AppSecurity\IsGranted(Role::TEACHER, exception=RequiresTeacherAccessException::class)
 */
final class StudentController extends Controller
{
    use  CurrentUserProviderTrait, JavascriptParametersTrait;

    /**
     * @Route("/", name="student_index", methods={"GET"})
     */
    public function index(UserEvaluatorInterface $userEvaluator): Response
    {
        $students = $this->getCurrentUserOrGuest()->getRealStudents()->getValues();
        $this->sortStudents($students);

        return $this->render('student/index.html.twig', [
            'students' => $students,
            'userEvaluator' => $userEvaluator,
        ]);
    }

    /**
     * @Route("/{id}/attempts/", name="student_attempts", methods={"GET"})
     * @IsGranted(UserVoter::SHOW_SOLVING_RESULTS, subject="student")
     */
    public function attempts(User $student): Response
    {
        $this->setJavascriptParameters([
            'getAttemptsUrl' => $this->generateUrl(ShowAttemptsCollectionSubscriber::ROUTE, [FilterUserValidationSubscriber::FIELD => $student->getUsername(), Attribute::FORMAT => Format::JSONDT]),
        ]);

        return $this->render('student/attempts.html.twig', [
            'student' => $student,
        ]);
    }

    /**
     * @Route("/{id}/examples/", name="student_examples", methods={"GET"})
     * @IsGranted(UserVoter::SHOW_SOLVING_RESULTS, subject="student")
     */
    public function examples(User $student): Response
    {
        $this->setJavascriptParameters([
            'getExamplesUrl' => $this->generateUrl(ShowExamplesCollectionSubscriber::ROUTE, [FilterUserValidationSubscriber::FIELD => $student->getUsername(), Attribute::FORMAT => Format::JSONDT]),
        ]);

        return $this->render('student/examples.html.twig', [
            'student' => $student,
        ]);
    }

    private function sortStudents(array &$students): void
    {
        usort($students, function (User $student1, User $student2): int {
            $attempt1 = $student1->getLastAttempt();
            $attempt2 = $student2->getLastAttempt();

            if (null !== $attempt1 && null !== $attempt2 && !$attempt1->getStartedAt()->isEqualTo($attempt2->getCreatedAt())) {
                return $attempt1->getStartedAt()->isGreaterThan($attempt2->getStartedAt()) ? -1 : 1;
            }

            if (null !== $attempt1 && null === $attempt2) {
                return -1;
            }

            if (null !== $attempt2 && null === $attempt1) {
                return 1;
            }

            return $student1->getRegisteredAt()->isGreaterThan($student2->getRegisteredAt()) ? -1 : 1;
        });
    }
}
