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
 * @Route("/profile")
 */
class ProfileController extends MainController
{
    /**
     * @Route("/", name="profile_index", methods="GET")
     */
    public function index(ProfileRepository $pR, UserLoader $ul, UserRepository $uR): Response
    {
$profiles=$pR->findByCurrentAuthor();

        return $this->render('profile/index.html.twig', [
'public'=>$pub=$pR->findByIsPublic(true),
'profiles' => $profiles,
"jsParams"=>[
"current"=>$uR->getCurrentProfile($ul->getUser())->getId(),
"canAppoint"=>$this->isGranted("APPOINT", array_merge($profiles, $pub)),
],
"all"=>($this->isGranted("ROLE_SUPER_ADMIN")) ? $pR->findAll() : [],
"pR"=>$pR,
]);
    }

    /**
     * @Route("/new", name="profile_new", methods="GET|POST")
     */
    public function new(Request $request, ProfileRepository $pR, UserLoader $ul): Response
    {
        $profile = new Profile();
$profile->SetDescription($pR->getTitle($profile));
        $form = $this->buildForm($profile);
        $form->handleRequest($request);
$canCreate=$this->isGranted("CREATE", $profile);

        if ($form->isSubmitted() && $form->isValid() && $canCreate) {
return $this->saveAndRedirect($profile, $form);
        }

        return $this->render('profile/new.html.twig', [
"jsParams"=>[
"canEdit"=>$canCreate,
],
            'profile' => $profile->setER($pR),
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
    public function edit(Request $request, Profile $profile, ProfileRepository $pR, UserLoader $ul): Response
    {
$this->denyAccessUnlessGranted("VIEW", $profile);
$profile->SetDescription($pR->getTitle($profile));
$canEdit=$this->isGranted("EDIT", $profile);
$canCopy=$this->isGranted("COPY", $profile);
$copying=$request->request->has("copy") && $canCopy;
if ($copying) {
($profile=clone($profile));
$profile->setAuthor($ul->getUser());
}
        $form = $this->buildForm($profile);
        $form->handleRequest($request);

        if (($form->isSubmitted()) && ($form->isValid()) && ($canEdit or $copying)) {
return $this->saveAndRedirect($profile, $form);
        }

        return $this->render('profile/edit.html.twig', [
"jsParams"=>[
"canEdit"=>$canEdit or $canCopy,
],
            'profile' => $profile->setER($pR),
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
     * @Route("/{id}/appoint", name="profile_appoint", methods="GET")
     */
public function appoint(Profile $profile, UserLoader $ul) {
$this->denyAccessUnlessGranted("APPOINT", $profile);
$u=$ul->getUser();
$u->setProfile($profile);
$this->em()->flush();
return $this->redirectToRoute("profile_index");
}

/**
     * @Route("/state", name="profile_state", methods="POST")
*/
public function state(Request $r, ProfileRepository $pR, UserRepository $uR, UserLoader $ul, Log $l) {
$pr=$r->request->get("profiles", []);
$l->debug(json_encode($pr));
$an=["app"=>[], "del"=>[]];

foreach ($pr as $id) {
$p=$pR->find($id);
$up=$uR->getCurrentProfile($ul->getUser());
$an["app"][$id]=["can"=>$this->isGranted("APPOINT", $p), "cur"=>$up===$p];
$an["del"][$id]=["can"=>$this->isGranted("DELETE", $p), "cur"=>$up===$p];
}

return $this->json($an);
}

private function buildForm($profile) {
$f=$this->createForm(ProfileType::class, $profile);

if ($this->isGranted("ROLE_ADMIN")) {
$f            ->add('isPublic')
            ->add('author')
            ->add('addTime');
}

return $f;
}

private function saveAndRedirect($profile, $form) {
$profile->normData();
$em=            $this->getDoctrine()->getManager();
$em->persist($profile);
$em->flush();

if ($this->isGranted("APPOINT", $profile)) {
$ul->getUser()->setProfile($profile);
$em->flush();
}

            return $this->redirectToRoute('profile_edit', ["id"=>$profile->getId()]);
}
}