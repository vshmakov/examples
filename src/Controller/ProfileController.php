<?php

namespace App\Controller;

use Psr\Log\LoggerInterface as Log;
use App\Entity\Profile;
use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserLoader;
use App\Repository\UserRepository;

/**
 * @Route("/attempt-profile")
 */
class ProfileController extends MainController
{
    /**
     * @Route("/", name="profile_index", methods="GET")
     */
    public function index(ProfileRepository $pR): Response
    {
        return $this->render('profile/index.html.twig', [
"jsParams"=>[
"profile_stage"=>$this->generateUrl("profile_stage"),
],
'public'=>$pR->findByIsPublic(true),
'profiles' => $pR->findByCurrentAuthor(),
"all"=>($this->isGranted("ROLE_SUPER_ADMIN")) ? $pR->findAll() : [],
"pR"=>$pR,
]);
    }

    /**
     * @Route("/new", name="profile_new", methods="GET|POST")
     */
    public function new(Request $request, ProfileRepository $pR): Response
    {
        $profile = new Profile();
$profile->SetDescription($pR->getTitle($profile));
        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->isGranted("CREATE", $profile)) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($profile->normPerc());
            $em->flush();

            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/new.html.twig', [
            'profile' => $profile,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="profile_show", methods="GET")
     */
    public function show(Profile $profile): Response
    {
$this->denyAccessUnlessGranted("VIEW", $profile);
        return $this->render('profile/show.html.twig', ['profile' => $profile]);
    }

    /**
     * @Route("/{id}/edit", name="profile_edit", methods="GET|POST")
     */
    public function edit(Request $request, Profile $profile, ProfileRepository $pR): Response
    {
$this->denyAccessUnlessGranted("VIEW", $profile);
$profile->SetDescription($pR->getTitle($profile));
        $form = $this->createForm(ProfileType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->isGranted("EDIT", $profile)) {
$profile->normPerc();
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('profile_edit', ['id' => $profile->getId()]);
        }

        return $this->render('profile/edit.html.twig', [
            'profile' => $profile,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="profile_delete", methods="GET")
     */
    public function delete(Request $request, Profile $profile): Response
    {
$this->denyAccessUnlessGranted("DELETE", $profile);
            $em = $this->getDoctrine()->getManager();
            $em->remove($profile);
            $em->flush();
        return $this->redirectToRoute('profile_index');
    }

    /**
     * @Route("/{id}/appoint", name="profile_appoint", methods="POST")
     */
public function appoint(Profile $profile, UserLoader $ul) {
$this->denyAccessUnlessGranted("APPOINT", $profile);
$u=$ul->getUser();
$u->setProfile($profile);
$this->em()->flush();
return $this->json(true);
}

/**
     * @Route("/stage", name="profile_stage", methods="POST")
*/
public function stage(Request $r, ProfileRepository $pR, UserRepository $uR, UserLoader $ul, Log $l) {
$pr=$r->request->get("profiles", []);
dump($r->request);
$l->debug(json_encode($pr));
$an=["app"=>[], "del"=>[]];

foreach ($pr as $id) {
$p=$pR->find($id);
$up=$uR->getSelfOrPublicProfile($ul->getUser());
$an["app"][$id]=["can"=>$this->isGranted("APPOINT", $p), "cur"=>$up===$p];
$an["del"][$id]=["can"=>$this->isGranted("DELETE", $p), "cur"=>$up===$p];
}

return $this->json($an);
}
}