<?php

namespace App\Controller;

use App\Controller\Traits\CurrentUserProviderTrait;
use App\Entity\User;
use  App\Entity\User\Role;
use App\Form\StudentType;
use App\Security\Annotation as AppSecurity;
use App\Security\Voter\UserVoter;
use App\Task\Homework\HomeworkProviderInterface;
use App\User\Student\Exception\RequiresStudentAccessException;
use App\User\Teacher\TeacherProviderInterface;
use App\Validator\Group;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/teacher")
 * @IsGranted(Role::USER)
 * @AppSecurity\IsGranted("ROLE_STUDENT", exception=RequiresStudentAccessException::class)
 */
final class TeacherController extends Controller
{
    use  CurrentUserProviderTrait;

    /**
     * @Route("/", name="teacher_index", methods={"GET"})
     */
    public function index(TeacherProviderInterface $teacherProvider): Response
    {
        $teachers = $teacherProvider->getTeachers();
        $this->sortTeachers($teachers);

        return $this->render('teacher/index.html.twig', [
            'teachers' => $teachers,
        ]);
    }

    /**
     * @Route("/{id}/appoint/", name="teacher_appoint", methods={"GET", "POST"})
     * @IsGranted(UserVoter::APPOINT_TEACHER, subject="teacher")
     */
    public function appoint(User $teacher, Request $request, ValidatorInterface $validator, HomeworkProviderInterface $homeworkProvider): Response
    {
        $currentUser = $this->getCurrentUserOrGuest();
        $currentUser->cleanSocialUsername();
        $errors = $validator->validate($currentUser, null, Group::STUDENT);

        if (!\count($errors)) {
            $currentUser->setTeacher($teacher);

            foreach ($homeworkProvider->getActualHomeworkOfCurrentUserTeacher() as $task) {
                $currentUser->addHomework($task);
            }

            $this->getDoctrine()
                ->getManager()
                ->flush($currentUser);

            return $this->redirectToRoute('account_index');
        }

        $form = $this->createForm(StudentType::class, $currentUser);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->getDoctrine()
                    ->getManager()
                    ->flush($currentUser);

                return $this->redirectToRoute('teacher_appoint', ['id' => $teacher->getId()]);
            }
        } else {
            foreach ($errors as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        return $this->render('teacher/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/disappoint/", name="teacher_disappoint", methods={"GET"})
     */
    public function disappoint(): Response
    {
        $currentUser = $this->getCurrentUserOrGuest();
        $currentUser->setTeacher(null);
        $this->getDoctrine()
            ->getManager()
            ->flush($currentUser);

        return $this->redirectToRoute('account_index');
    }

    private function sortTeachers(array &$teachers): void
    {
        $currentUser = $this->getCurrentUserOrGuest();

        usort($teachers, function (User $teacher1, User $teacher2) use ($currentUser): int {
            if ($currentUser->isStudentOf($teacher1)) {
                return -1;
            }

            if (($currentUser->isStudentOf($teacher2))) {
                return 1;
            }

            $studentsCount1 = $teacher1->getStudents()->count();
            $studentsCount2 = $teacher2->getStudents()->count();

            if ($studentsCount1 !== $studentsCount2) {
                return $studentsCount1 > $studentsCount2 ? -1 : 1;
            }

            return $teacher1->getRegisteredAt()->getTimestamp() <= $teacher2->getRegisteredAt()->getTimestamp() ? -1 : 1;
        });
    }
}
