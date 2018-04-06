<?php

namespace AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Controller\MainController;

/**
*@Route("/api")
*/
class ApiController extends MainController {
/**
*@Route("/tries/{id}/answer", name="answerExample")
*@Method("POST")
*/
public function answerExampleAction($id, Request $request) {
$try=er('t')->getCurrentUserTryByIdOrNull($id) ?? throwNotFoundExseption();
if (!$try->isActual()) return $this->finishTry();
$isRight=er('e')->getLastOrNewExampleByTry($try)->setAnswer($request->request->get('answer'));
em()->flush();
if (!$try->isActual()) return $this->finishTry();
er('e')->getNewExampleByTry($try);
em()->flush();

return $this->json([
'isRight'=>$isRight, 
'tryData'=>$try->getCurrentData()
]);//
}

private function finishTry() {
return $this->json([
'finishTry'=>true
]);
}

/**
*@Route("/profiles/choose", name="chooseProfile")
*@Method("POST")
*/
public function chooseProfileAction(Request $request) {
$profile=er('p')->getCurrentUserOrPublicProfileByIdOrNull($request->request->get('profileId')) ?? throwNotFoundExseption();
er('u')->getCurrentUserOrGuest()->setProfile($profile);
em()->flush();
return $this->json($profile->getId());
}

}