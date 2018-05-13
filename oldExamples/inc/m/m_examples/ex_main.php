<?php
abstract class ex_main extends ex_base {
public function check_last_ex() {
$key=$this->mysql->extract_value(sprintf("SELECT `ex_right` FROM `examples` 
WHERE `id_try` = '%1\$u'AND `id_ex` IN 
(SELECT max(id_ex) FROM `examples`
WHERE `id_try` = '%1\$u') ", 
ID_TRY));

return ($key===false || $key) ? true : false;
}

public function answer($answer) {
$example=$this->return_last_example();
$right=((double) $answer===(double) $this->solve($example));
$this->mysql->update('examples', array('ex_time'=>date('Y-m-d H:i:s'), 'ex_answer'=>$answer, 'ex_right'=>(int) $right), 
sprintf("`id_ex` = '%u'", $this->return_last_ex_id()));

if (!$right) $this->mysql->insert('examples', array('id_try'=>$this->return_try(), 'ex_time'=>date('Y-m-d H:i:s'), 'ex_first_num'=>$example['a'], 'opernum'=>$example['opernum'], 'ex_second_num'=>$example['b']));

return true;
}

public function make_example() {
$opernum=$this->make_opernum();
$method_name='make_'.self::OPERATIONS[$opernum]['name'].'_ex';
$arr=$this->$method_name();
$arr['opernum']=$opernum;
return $arr;
}

}