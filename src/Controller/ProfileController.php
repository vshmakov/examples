<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attempt\Profile\ProfileInitializerInterface;
use App\Attempt\Profile\ProfileProviderInterface;
use App\Controller\Traits\CurrentUserProviderTrait;
use App\Controller\Traits\ProfileTrait;
use App\Entity\Profile;
use App\Form\ProfileType;
use App\Security\Voter\CurrentUserVoter;
use App\Security\Voter\ProfileVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use  Symfony\Component\HttpFoundation\Request;
use  Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile")
 */
final class ProfileController extends Controller
{
    use CurrentUserProviderTrait, ProfileTrait;

    /**
     * @Route("/", name="profile_index", methods={"GET"})
     */
    public function index(ProfileProviderInterface $profileProvider): Response
    {
        $publicProfiles = $profileProvider->getPublicProfiles();
        $userProfiles = $profileProvider->getCurrentUserProfiles();
        array_map(function (array &$profiles) use ($profileProvider): void {
            $this->sortProfiles($profiles, $profileProvider);
        }, [&$publicProfiles, &$userProfiles]);

        return $this->render('profile/index.html.twig', [
            'publicProfiles' => $publicProfiles,
            'userProfiles' => $userProfiles,
            'profileProvider' => $profileProvider,
        ]);
    }

    /**
     * @Route("/new/", name="profile_new", methods="GET|POST")
     * @IsGranted(CurrentUserVoter::CREATE_PROFILES)
     */
    public function new(Request $request, ProfileInitializerInterface $profileInitializer): Response
    {
        $profile = $profileInitializer->initializeNewProfile();
        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveAndAppointProfile($profile);

            return $this->redirectToRoute('profile_edit', ['id' => $profile->getId()]);
        }

        return $this->render('profile/new.html.twig', [
            'profile' => $profile,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit/", name="profile_edit", methods="GET|POST")
     * @IsGranted(ProfileVoter::EDIT, subject="profile")
     */
    public function edit(Profile $profile, Request $request, ProfileProviderInterface $profileProvider): Response
    {
        $profileTitle = $profile->getDescription();
        $form = $this->createForm(ProfileType::class, $profile)
            ->add('copy', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $redirectAction = 'profile_show';

            if ($form->get('copy')->isClicked()) {
                $profile = $this->cloneProfile($form->getData());
                $redirectAction = 'profile_edit';
            }

            $this->saveAndAppointProfile($profile);

            return $this->redirectToRoute($redirectAction, ['id' => $profile->getId()]);
        }

        return $this->render('profile/edit.html.twig', [
            'profile' => $profile,
            'profileTitle' => $profileTitle,
            'isCurrentProfile' => $profileProvider->isCurrentProfile($profile),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/", name="profile_show", methods={"GET"})
     * @IsGranted(ProfileVoter::VIEW, subject="profile")
     */
    public function show(Profile $profile, ProfileProviderInterface $profileProvider): Response
    {
        return $this->render('profile/show.html.twig', [
            'profile' => $profile,
            'isCurrentProfile' => $profileProvider->isCurrentProfile($profile),
        ]);
    }

    /**
     * @Route("/{id}/copy/", name="profile_copy", methods={"GET"})
     * @IsGranted(ProfileVoter::COPY, subject="profile")
     */
    public function copy(Profile $profile): RedirectResponse
    {
        $targetProfile = $this->cloneProfile($profile);
        $this->saveAndAppointProfile($targetProfile);

        return $this->redirectToRoute('profile_edit', ['id' => $targetProfile->getId()]);
    }

    /**
     * @Route("/{id}/delete/", name="profile_delete", methods={"DELETE"})
     * @IsGranted(ProfileVoter::DELETE, subject="profile")
     */
    public function delete(Request $request, Profile $profile): RedirectResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($profile);
        $entityManager->flush($profile);

        return $this->redirectToRoute('profile_index');
    }

    /**
     * @Route("/{id}/appoint/", name="profile_appoint", methods={"GET"})
     * @IsGranted(ProfileVoter::APPOINT, subject="profile")
     */
    public function appoint(Profile $profile): RedirectResponse
    {
        $currentUser = $this->getCurrentUserOrGuest();
        $currentUser->setProfile($profile);
        $this->getDoctrine()
            ->getManager()
            ->flush($currentUser);

        return $this->redirectToRoute('profile_index');
    }

    private function sortProfiles(array &$profiles, ProfileProviderInterface $profileProvider): void
    {
        usort($profiles, function (Profile $profile1, Profile $profile2) use ($profileProvider): int {
            if ($profileProvider->isCurrentProfile($profile1)) {
                return -1;
            }

            if ($profileProvider->isCurrentProfile($profile2)) {
                return 1;
            }

            return $profile1->getCreatedAt()->getTimestamp() <= $profile2->getCreatedAt()->getTimestamp() ? -1 : 1;
        });
    }

    private function cloneProfile(Profile $sourceProfile): Profile
    {
        $targetProfile = clone $sourceProfile;
        $targetProfile->setAuthor($this->getCurrentUserOrGuest());

        return $targetProfile;
    }
}
