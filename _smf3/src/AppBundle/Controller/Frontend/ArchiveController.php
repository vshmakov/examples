<?php

namespace AppBundle\Controller\Frontend;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as notFoundException;
use AppBundle\Controller\MainController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Tries;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

 /**
*@Route("/archive")
*/
class ArchiveController extends MainController {

/**
*@Route("", name="archive")
*/
public function indexAction() {
return $this->render('frontend/archive/index.html.twig', [
'title'=>"Архив попыток",
'tries'=>er('t')->getAllCurrentUserTries()
]);
}

/**
*@Route("/{id}", name="history", requirements={"id": "\d+"})
*/
public function historyAction ($id) {
$try=er('t')->getCurrentUserTryByIdOrNull($id) ?? throwNotFoundExseption();
$examples=er('e')->findByTry($try);

return $this->render('frontend/archive/history.html.twig', [
'title'=>"Попытка №{$try->getId()}",
'try'=>$try,
'examples'=>$examples
]);
}

}