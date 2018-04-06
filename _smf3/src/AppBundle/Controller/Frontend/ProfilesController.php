<?php

namespace AppBundle\Controller\Frontend;

use AppBundle\Controller\MainController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use AppBundle\Form\ProfileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

 /**
*@Route("/profiles")
*/
class ProfilesController extends MainController {

/**
*@Route("", name="profilesList")
*/
public function indexAction() {
$rCh=$this->rightsChecker;
return $this->render('frontend/profiles/index.html.twig', [
'JSParams'=>[
'currentProfile'=>($p=er('p')->getCurrentUserOrPublicProfile()) ? $p->getId() : null,
'chooseProfilePath'=>$this->generateUrl('chooseProfile'),
'canChooseProfiles'=>$rCh->canChooseProfiles(),
'canDeleteProfiles'=>$rCh->canDeleteProfiles(),
'canCreateProfiles'=>$rCh->canCreateProfiles(),
],
'title'=>"Профили",
'publicProfiles'=>er('p')->findByIsPublic(true),
'userProfiles'=>er('p')->getAllCurrentUserProfiles()
]);
}

/**
*@Route("/{id}/edit", name="profileById", requirements={"id": "\d+"})
*/
public function profileAction ($id, Request $request) {
$profile=er('p')->getCurrentUserOrPublicProfileByIdOrNull($id) ?? throwNotFoundExseption();
$rCh=$this->rightsChecker;
$values=$request->request->get('profile');

if ($rCh->canCreateProfiles() && ($values['saveAsNewProfile'] ?? false)) {
$p=a('p');
$profile=er('p')->initialize(new $p);
}

if ($rCh->canDeleteProfiles() && ($values['deleteProfile'] ?? false)) {
em()->remove($profile);
em()->flush();
return $this->redirectToRoute('profilesList');
}

$form=$this->createForm(ProfileType::class, $profile);
if ($rCh->canCreateProfiles()) $form->add('saveAsNewProfile', SubmitType::class, ['attr'=>['value'=>1]]);
if ($rCh->canDeleteProfiles()) $form->add('deleteProfile', SubmitType::class, ['attr'=>['value'=>1]]);
if ($rCh->canAppointPublicProfiles()) $form->add('isPublic', CheckboxType::class, ['required'=>false]);

$responce=($rCh->canEditProfiles()) ? $this->processProfileForm($form->handleRequest($request)) : null;

return $responce ?? $this->render('frontend/profiles/edit.html.twig', [
'JSParams'=>[
'canEditProfiles'=>$rCh->canEditProfiles(),
],
'title'=>$profile->getDescription(),
'form'=>$form->createView(),
'profile'=>$profile,
'rightsChecker'=>$rCh,
]);
}

private function processProfileForm($form) {
if (($form->isSubmitted()) && ($form->isValid())) {
($profile=$form->getData());
$profile->normalizePercents();
em()->persist($profile);
em()->flush();
return $this->redirectToRoute('profilesList');
}
}

/**
*@Route("/new", name="createProfile")
*/
public function createProfileAction(Request $request) {
$rCh=$this->rightsChecker;
$can=$rCh->canEditProfiles() or throwAccessDeniedException();

$p=a('p');
$form=$this->createForm(ProfileType::class, new $p());
if ($rCh->canAppointPublicProfiles()) $form->add('isPublic', CheckboxType::class, ['required'=>false]);

return ($this->processProfileForm($form->handleRequest($request)))
?? $this->render('frontend/profiles/edit.html.twig', [
'JSParams'=>[
'canEditProfiles'=>$can,
],
'title'=>"Создать профиль",
'form'=>$form->createView(),
]);
}

/**
*@Route("/{id}/delete", name="deleteProfile")
*/
public function deleteProfileAction($id) {
$profile=er('p')->getCurrentUserProfileByIdOrNull($id) ?? throwNotFoundExseption();
if (!er('p')->isCurrentUserProfile($profile)) em()->remove($profile);
em()->flush();
return $this->redirectToRoute('profilesList');
}

}