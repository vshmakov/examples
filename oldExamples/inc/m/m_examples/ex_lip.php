<?php
abstract class ex_lip {
const OPERATIONS=array('1'=>array('name'=>'add', 'sign'=>'+'), array('name'=>'sub', 'sign'=>'-'), 
array('name'=>'mult', 'sign'=>'*'), array('name'=>'div', 'sign'=>':'));
const SET_NAMES=array('addproc', 'available_time', 'divmin', 'divproc', 'maxadd', 'maxdiv', 'maxmult', 'maxsub', 'minadd', 'mindiv', 'minmult', 'minsub', 'multproc', 'number', 'submin', 'subproc');

protected function make_mult_ex() {
extract($this->extract_settings(
array('minmult', 'maxmult')));

$ex['a']=mt_rand($minmult, $maxmult);
$ex['b']=mt_rand($minmult, $maxmult);

return $ex;
}

protected function make_div_ex() {
extract($this->extract_settings(
array('mindiv', 'maxdiv', 'divmin')));


$to=((int) $mindiv!==0) ? $maxdiv/$mindiv : 0;
$c=mt_rand($divmin, (int) $to);
$from=((int) $mindiv!==0) ? $mindiv : 1;
$to=($c!==0) ? $maxdiv/$c : 1;
$ex['b']=mt_rand($from, (int) $to);
$ex['a']=$c*$ex['b'];

return $ex;
}

protected function make_add_ex() {
$settings=$this->get_add_settings();
$arr['a']=mt_rand($settings['minadd'], $settings['maxadd']);
$arr['b']=mt_rand($settings['minadd'], $settings['maxadd']);
$arr['c']=$arr['a']+$arr['b'];
return $arr;
}

protected function make_sub_ex() {
$settings=$this->get_sub_settings();
/*$arr['a']=mt_rand($settings['minsub'], $settings['max']);
$min= ($settings['min']>$arr['a']) ? $settings['min'] : $arr['a'];
$arr['b']=mt_rand($min+$settings['minsub'], $settings['max']);*/

$arr['a']=mt_rand($settings['minsub']+$settings['submin'], $settings['maxsub']);
$arr['c']=mt_rand($settings['submin'], $arr['a']-$settings['minsub']);
$arr['b']=$arr['a']-$arr['c'];
return $arr;
}

protected function make_opernum() {
$operprocs=array('1'=>'addproc', 'subproc', 'multproc', 'divproc');
$procvalues=$this->extract_settings($operprocs);

$rand=mt_rand(1, 100);
$k=0;
$opernum=1;
for ($i=1; $i<=4; $i++) {
$k+=$procvalues[$operprocs[$i]];
if ($rand<$k) {
$opernum=$i;
break;
}
}

return $opernum;
}

protected function solve(array $example) {
extract($example);
switch ((int) $opernum) {
case 1:
return $a+$b;
break;
case 2:
return $a-$b;
break;
case 3:
return $a*$b;
break;
case 4:
return $a/$b;
break;
}
}

protected function extract_time($date_time) {
return m_site_pages::get_instance()->extract_time($date_time);
}

}