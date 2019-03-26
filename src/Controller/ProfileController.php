<?php

namespace App\Controller;

use App\Attempt\Profile\NormalizerInterface as ProfileNormalizerInterface;
use App\Attempt\Profile\ProfileProviderInterface;
use  App\Controller\Traits\BaseTrait;
use App\Controller\Traits\CurrentUserProviderTrait;
use App\Entity\Profile;
use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use App\Security\Voter\ProfileVoter;
use App\Service\UserLoader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use  Symfony\Component\HttpFoundation\Response;
use  Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/profile")
 */
final class ProfileController extends Controller
{
    use BaseTrait, CurrentUserProviderTrait;

    /**
     * @Route("/", name="profile_index", methods={"GET"})
     */
    public function index(ProfileProviderInterface $profileProvider): Response
    {
        $publicProfiles = $profileProvider->getPublicProfiles();
        $teacherProfiles = $profileProvider->getTeacherProfiles();
        $userProfiles = $profileProvider->getUserProfiles();
        array_map(function (array &$profiles) use ($profileProvider): void {
            $this->sortProfiles($profiles, $profileProvider);
        }, [&$publicProfiles, &$teacherProfiles, &$userProfiles]);

        return $this->render('profile/index.html.twig', [
            'publicProfiles' => $publicProfiles,
            'teacherProfiles' => $teacherProfiles,
            'userProfiles' => $userProfiles,
            'profileProvider' => $profileProvider,
        ]);
    }

    private function sortProfiles(array &$profiles, ProfileProviderInterface $profileProvider): void
    {
        usort($profiles, function (Profile $profile1, Profile $profile2) use ($profileProvider): int {
            return $profileProvider->isCurrentProfile($profile1) ? -1 : 1;
        });
    }

    /**
     * @Route("/new", name="profile_new", methods="GET|POST")
     */
    public function new(Request $request, ProfileRepository $profileRepository, UserLoader $userLoader, ProfileNormalizerInterface $normalizer): Response
    {
        $profile = new Profile();
        $profile->SetDescription($profileRepository->getTitle($profile))
            ->setAuthor($userLoader->getUser());
        $normalizer->normalize($profile);
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
     * @IsGranted(ProfileVoter::EDIT, subject="profile")
     */
    public function edit(Profile $profile, Request $request, ProfileRepository $profileRepository, UserLoader $userLoader): Response
    {
        $profileTitle = $profileRepository->getTitle($profile);
        $profile->SetDescription($profileTitle);
        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()
                ->getManager()
                ->flush();

            return $this->redirectToRoute('profile_show', ['id' => $profile->getId()]);
        }

        return $this->render('profile/edit.html.twig', [
            'profileTitle' => $profileTitle,
            'profile' => $profile,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/", name="profile_show", methods={"GET"})
     * @IsGranted(ProfileVoter::VIEW, subject="profile")
     */
    public function show(Profile $profile, NormalizerInterface $normalizer): Response
    {
        return $this->render('profile/show.html.twig', [
            'profile' => $profile,
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
