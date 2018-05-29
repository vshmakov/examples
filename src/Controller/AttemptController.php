<?php

namespace App\Controller;

use App\Form\ProfileType;
use App\Repository\ProfileRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\EntityManagerInterface as EM;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Attempt;
use App\Repository\AttemptRepository as AttR;
use App\Repository\ExampleRepository as ExR;
use Symfony\Component\Routing\Annotation\Route;

 /**
* @Route("/attempt")
*/
class AttemptController extends MainController {
private $attR;

public function __construct(AttR $attR) {
$this->attR=$attR;
}

/**
* @Route("/", name="attempt_index")
*/
public function index(AttR $r) {
return $this->render('attempt/index.html.twig', [
"jsParams"=>[
"api_attempt_index_table"=>$this->generateUrl("api_attempt_index_table"),
],
"attempts"=>$r->findAllByCurrentUser(),
"attR"=>$r,
]);
}

/**
*@Route("/{id}/show", name="attempt_show", requirements={"id": "\d+"})
*/
public function show(Attempt $att, ExR $exR, AttR $attR) {
$this->denyAccessUnlessGranted('VIEW', $att);
return $this->render('attempt/show.html.twig', [
"att"=>$att->setER($attR),
"examples"=>$exR->findByAttempt($att),
"exR"=>$exR,
]);
}

/**
*@Route("/{id}", name="attempt_solve", requirements={"id": "\d+"})
*/
public function solve(Attempt $att, ExR $exR, AttR $attR, \Symfony\Component\HttpFoundation\Session\Session $s) {
    if (!$this->isGranted("SOLVE", $att)) {
if ($this->isGranted("VIEW", $att)) return $this->redirectToRoute('attempt_show', ['id'=>$att->getId()]);
else throw new AccessDeniedException();
}
dump($att->getExamplesCount());
$exR->findLastUnansweredByAttemptOrGetNew($att);
return $this->render('attempt/solve.html.twig', [
"jsParams"=>[
"attData"=>$att->setER($attR)->getData(),
'attempt_answer'=>$this->generateUrl('attempt_answer', ['id'=>$att->getId()])
],
"att"=>$att,
]);//
}

/**
*@Route("/last", name="attempt_last")
*/
public function last(AttR $attR) {
if ($att=$attR->findLastActualByCurrentUser()) return $this->redirectToRoute('attempt_solve', ['id'=>$att->getId()]);
return $this->render("attempt/last.html.twig");
}

/**
*@Route("/new", name="attempt_new")
*/
public function new(AttR $attR) {
return $this->redirectToRoute('attempt_solve', ['id'=>$attR->getNewByCurrentUser()->getId()]);
}

/**
*@Route("/{id}/answer", name="attempt_answer", methods="POST")
*/
public function answer(Attempt $att, Request $request, ExR $exR, EM $em, AttR $attR) {
if (!$this->isGranted("ANSWER", $att)) return $this->json(['finish'=>true]);

$ex=$exR->findLastUnansweredByAttempt($att);
$an=(float) $request->request->get('answer');
$ex->setAnswer($an);
$em->flush();

$finish=!$this->isGranted("SOLVE", $att);
if (!$finish) $exR->getNew($att);
return $this->json([
'isRight'=>$ex->isRight(), 
'finish'=>$finish,
'attData'=>$att->setER($attR)->getData(),
]);//
}

private function getDataByAtt($att) {
return [];
}

/**
*@Route("/{id}/profile", name="attempt_profile")
*/
public function profile(Attempt $att, ProfileRepository $pR, AttR $attR) {
$this->denyAccessUnlessGranted("VIEW", $att);
$p=$att->getSettings()->setER($pR);

return $this->render("attempt/profile.html.twig", [
"jsParams"=>[
"canEdit"=>false,
],
"profile"=>$p,
"form"=>$this->createForm(ProfileType::class, $p)->createView(),
"att"=>$att->setER($attR),
]);
}

/**
*@Route("/api/index-table", name="api_attempt_index_table")
*/
public function apiIndexTable(Request $req, AttR $attR) {
$d=[];
$q=$req->query;
$s=$q->get("start");
$l=$q->get("length");

foreach ($attR->findAllByCurrentUserAndLimit($s, $l) as $att) {
$ks="title addTime solvedTime solvedExamplesCount errorsCount rating";
$row=createNumArr(getKeiesFromEntity($ks, $att->setER($attR)));

$row[0]=sprintf('<a href="%s">%s</a>', $this->generateUrl("attempt_show", ["id"=>$att->getId()]), $row[0]);
$row[1]="".$row[1];
$row[2]=sprintf("%s / %s (%s сек/пример)", $row[2]->minSecFormat(), $att->getMaxTime()->minSecFormat(), $att->getAverSolveTime()->getTimestamp());
$row[3]=sprintf("%s / %s", $row[3], $att->getExamplesCount());
$o=$row[5];
$c="red";
if ($o==3) $c="orange";
if ($o==4) $c="yellow";
if ($o==5) $c="green";
$row[5]=sprintf('<span style="background: %s;">%s</span>', $c, $o);
$d[]=$row;
}

return $this->json([
"draw"=>$q->get("draw"),
"recordsTotal"=>$c=$attR->CountByCurrentUser(),
"recordsFiltered"=>$c,
"data"=>$d,
]);
}
}