<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use App\Service\UserLoader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile")
 */
class ProfileController extends Controller
{
    use BaseTrait;

    /**
     * @Route("/", name="profile_index", methods="GET")
     */
    public function index(ProfileRepository $profileRepository, UserLoader $userLoader, UserRepository $userRepository): Response
    {
        $publicProfiles = $profileRepository->findByIsPublic(true);
        $userProfiles = $profileRepository->findByCurrentAuthor();
        $teacherProfiles = $profileRepository->findByCurrentUserTeacher();

        return $this->render('profile/index.html.twig', [
            'public' => $publicProfiles,
            'teacherProfiles' => $teacherProfiles,
            'profiles' => $userProfiles,
            'canAppoint' => $can = $this->isGranted('PRIV_APPOINT_PROFILES'),
            'pR' => $profileRepository,
            'jsParams' => [
                'current' => $userRepository->getCurrentProfile($userLoader->getUser())->getId(),
                'canAppoint' => $can,
            ],
        ]);
    }

    /**
     * @Route("/new", name="profile_new", methods="GET|POST")
     */
    public function new(Request $request, ProfileRepository $profileRepository, UserLoader $userLoader): Response
    {
        $profile = new Profile();
        $profile->SetDescription($profileRepository->getTitle($profile))
            ->setAuthor($userLoader->getUser());
        $form = $this->buildForm($profile);
        $form->handleRequest($request);
        $canCreate = $this->isGranted('CREATE', $profile);

        if ($form->isSubmitted() && $form->isValid() && $canCreate) {
            return $this->saveAndRedirect($profile, $form, $userLoader);
        }

        return $this->render('profile/new.html.twig', [
            'jsParams' => [
                'canEdit' => $canCreate,
            ],
            'profile' => $profile->setEntityRepository($profileRepository),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="profile_edit", methods="GET|POST")
     */
    public function edit(Request $request, Profile $profile, ProfileRepository $profileRepository, UserLoader $userLoader): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $profile);
        $profile->SetDescription($profileRepository->getTitle($profile));
        $canEdit = $this->isGranted('EDIT', $profile);
        $canCopy = $this->isGranted('COPY', $profile);
        $copying = $request->request->has('copy') && $canCopy;

        if ($copying) {
            ($profile = clone $profile);
            $profile->setAuthor($userLoader->getUser());
        }

        $form = $this->buildForm($profile, $copying);

        if ($request->isMethod('POST')) {
            $inputs = $request->request->get($form->getName());

            if ($copying) {
                foreach (arr('isPublic author addTime') as $fieldName) {
                    if (isset($inputs[$fieldName])) {
                        unset($inputs[$fieldName]);
                    }
                }
            }

            $form->submit($inputs);
        }

        if (($form->isSubmitted()) && ($form->isValid()) && ($canEdit or $copying)) {
            return $this->saveAndRedirect($profile, $form, $userLoader);
        }

        return $this->render('profile/edit.html.twig', [
            'jsParams' => [
                'canEdit' => $canEdit or $canCopy,
            ],
            'profile' => $profile->setEntityRepository($profileRepository),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="profile_delete", methods="DELETE")
     */
    public function delete(Request $request, Profile $profile, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $profile);

        foreach ($userRepository->findByProfile($profile) as $user) {
            $user->setProfile(null);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();
        $entityManager->remove($profile);
        $entityManager->flush();

        return $this->redirectToRoute('profile_index');
    }

    /**
     * @Route("/{id}/appoint", name="profile_appoint", methods="GET")
     */
    public function appoint(Profile $profile, UserLoader $userLoader)
    {
        if ($this->isGranted('APPOINT', $profile)) {
            $user = $userLoader->getUser();
            $user->setProfile($profile);
            $this->getEntityManager()->flush();
        }

        return $this->redirectToRoute('profile_index');
    }

    private function buildForm(Profile $profile, bool $copying = false)
    {
        $form = $this->createForm(ProfileType::class, $profile);

        if ($this->isGranted('ROLE_ADMIN') && !$copying) {
            $form->add('isPublic')
                ->add('author')
                ->add('addTime');
        }

        return $form;
    }

    private function saveAndRedirect(Profile $profile, $form, UserLoader $userLoader)
    {
        $profile->normalize();
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($profile);
        $entityManager->flush();

        if ($this->isGranted('APPOINT', $profile)) {
            $userLoader->getUser()->setProfile($profile);
            $entityManager->flush();
        }

        return $this->redirectToRoute('profile_edit', [
            'id' => $profile->getId(),
        ]);
    }
}
