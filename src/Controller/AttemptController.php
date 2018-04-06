<?php

namespace App\Controller;

use App\Entity\Attempt;
use App\Repository\AttemptRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;

 /**
*@Route("/attempts")
*/
class AttemptController extends MainController {
/**
*@Route("/", name="attempt_index")
*/
public function index(AttemptRepository $r) {
return $this->render('attempt/index.html.twig', [
"attempts"=>$r->findAllByCurrentUser()
]);
}

/**
*@Route("/{id}/show", name="attempt_show", requirements={"id": "\d+"})
*/
public function show(Attempt $att) {
$this->canOrAcc('VIEW', $att);
$try=er('t')->getCurrentUserTryByIdOrNull($id) ?? throwNotFoundExseption();
$examples=er('e')->findByTry($try);

return $this->render('frontend/archive/history.html.twig', [
'title'=>"Попытка №{$try->getId()}",
'try'=>$try,
'examples'=>$examples
]);
}

/**
*@Route("/{id}", name="attempt_solve", requirements={"id": "\d+"})
*/
public function solve(Attempt $att, \Symfony\Component\HttpFoundation\Session\Session $s) {
$try=er('t')->getCurrentUserTryByIdOrNull($id) ?? throwNotFoundExseption();
    if (!$try->isActual()) return $this->redirectToRoute('history', ['id'=>$try->getId()]);
var_dump(er('s')->getCurrentUserSessionOrNull()->getSid());
return $this->render('frontend/tries/index.html.twig', [
'title'=>'Решение',
'JSParams'=>[
'tryData'=>$try->getCurrentData(),
'answerRoute'=>$this->generateUrl('answerExample', ['id'=>$try->getId()])
]
]);//
}

/**
*@Route("/last", name="lastTry")
*/
public function lastAction() {
$try=(($t=er('t')->getCurrentUserLastTryOrNull()) && $t->isActual()) ? $t : er('t')->getNewTry();
return $this->redirectToRoute('tryById', 
['id'=>$try->getId()]);
}

/**
*@Route("/new", name="newTry")
*/
public function newAction() {
return $this->redirectToRoute('tryById', ['id'=>er('t')->getNewTry()->getId()]);
}

}