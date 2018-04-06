<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ExamplesRepository extends EntityRepository {
const OPERS=[1=>'Add', 'Sub', 'Mult', 'Div'];

public function getLastOrNewExampleByTry($try) {
return $this->getLastExampleByTryOrNull($try) ?? $this->getNewExampleByTry($try);
}

public function getLastExampleByTryOrNull($try) {
return createQuery(
'select e from  %1$s e
where e.try= :try and e.id in
(select max(e1.id) from %1$s e1 where e1.try = :try)', ['e']
)->setParameter('try', $try)->getOneOrNullResult();
}

public function getNewExampleByTry($try) {
$e=a('e');
$example=new $e;
$this->initialize($example, $try);
em()->persist($example);
em()->flush();
return $example;
}

protected function initialize($ex, $try) {
foreach($this->getRandomExample($try->getSettings()) as $key=>$val) {
call_user_func([$ex, 'set'.ucfirst($key)], $val);
}

$ex->setTry($try);
}

protected function getRandomExample($settings) {
$sign=$this->getRandSign($settings);
$method='getRand'.self::OPERS[$sign].'Example';
$ex=$this->$method($settings);
return $ex+['sign'=>$sign];
}

private function getRandSign($settings) {
$sign=1;
$curPerc=0;
$rand=mt_rand(1, 100);

foreach (self::OPERS as $key=>$oper) {
$method='get'.$oper.'Perc';
$curPerc+=$settings->$method();
if ($rand<=$curPerc) {
$sign=$key;
break;
}
}

return $sign;
}

private function getRandAddExample($settings) {
$a=mt_rand($settings->getAddMin(), $settings->getAddMax());
$b=mt_rand($settings->getAddMin(), $settings->getAddMax());

return ['first'=>$a, 'second'=>$b];
}

private function getRandSubExample($settings) {
$subMin=$settings->getSubMin();
$subMax=$settings->getSubMax();
$minSub=$settings->getMinSub();

$a=mt_rand(subMin+$minSub, $subMax);
$c=mt_rand($minSub, $a-$subMin);
$b=$a-$c;

return ['first'=>$a, 'second'=>$b];
}

private function getRandMultExample($settings) {
$min=$settings->getMultMin();
$max=$settings->getMultMax();

$a=mt_rand($min, $max);
$b=mt_rand($min, $max);

return ['first'=>$a, 'second'=>$b];
}

private function getRandDivExample($settings) {
$divMin=$settings->getDivMin();
$divMax=$settings->getDivMax();
$minDiv=$settings->getMinDiv();

$to=((int) $divMin!==0) ? $divMax/$divMin: 0;
$c=mt_rand($minDiv, (int) $to);
$from=((int) $divMin!=0) ? $divMin: 1;
$to=($c!==0) ? $divMax/$c : 1;
$b=mt_rand($from, (int) $to);
$a=$c*$b;

return ['first'=>$a, 'second'=>$b];
}

}