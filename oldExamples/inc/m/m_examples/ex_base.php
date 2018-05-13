<?php
abstract class ex_base extends ex_lip {
protected $settings;
private $last_ex_id;
private $last_try;
private $id_try;

public function is_try_relevant() {
return ($this->return_try() &&
(int) $this->get_ex_count()<(int) $this->extract_settings(array('number'))['number'] &&
time()<=$this->get_deadline());
}

public function is_such_prof() {
return (boolean) $this->mysql->extract_value(sprintf("SELECT count(*) FROM `profiles`
WHERE `id_prof` = '%u' AND `id_user` = '%u'", 
ID_PROF, $_SESSION['user']['id_user']));
}

public function get_ex_count() {
return $this->mysql->extract_value(sprintf("SELECT 
(SELECT count(*) FROM `examples`
WHERE `ex_answer` IS NOT NULL AND `id_try` = '%1\$u')
-
(SELECT count(*) FROM `examples`
WHERE `id_try` = '%1\$u' AND `ex_right` = '0')", 
ID_TRY));
}

public function is_such_try() {
return (boolean) $this->mysql->extract_value(sprintf("SELECT `id_try` FROM `tries`
JOIN `profiles` USING(`id_prof`)
WHERE `id_try` = '%u' AND `id_user` = '%u'", 
ID_TRY, $_SESSION['user']['id_user']));
}

public function open_new_try() {
$current_prof=$this->mysql->extract_value(sprintf("SELECT `id_prof` FROM `profs2users`
WHERE `id_user` = '%u'", 
$_SESSION['user']['id_user']));

$id_try=$this->mysql->insert('tries', 
array('id_prof'=>$current_prof, 'try_start'=>date('Y-m-d H:i:s')));
$this->mysql->insert('tries2sessions', array('id_try'=>$id_try, 'sid'=>$_SESSION['sid']));

return $id_try;
}

public function return_try() {
if($this->id_try) return $this->id_try;

$id_try=$this->mysql->extract_value(sprintf("SELECT `id_try` FROM `tries2sessions`
WHERE `id_try` = '%u' AND `sid` = '%s'", 
ID_TRY, $this->mysql->escape($_SESSION['sid']), $this->get_ex_count()));

$this->id_try=$id_try;
return $id_try;
}

public function return_last_try() {
if ($this->last_try) return $this->last_try;

$id_try=$this->mysql->extract_value(sprintf("SELECT max(id_try) FROM `tries2sessions` 
WHERE `sid` = '%s'", 
$this->mysql->escape($_SESSION['sid'])));

$this->last_try=$id_try;
return $id_try;
}

public function return_last_example() {
return $this->return_example($this->return_last_ex_id());
}

protected function return_example($id_ex) {
return $this->mysql->extract_row(sprintf("SELECT `id_ex`, `ex_first_num` as `a`, `opernum`, `ex_second_num` as `b` FROM `examples`
WHERE `id_ex` = '%u'", $id_ex));
}

protected function return_last_ex_id() {
if ($this->last_ex_id) return $this->last_ex_id;

return $this->mysql->extract_value(sprintf("SELECT max(id_ex) FROM `examples`
WHERE `id_try` = '%u'", $this->return_try()));
}

protected function get_settings() {
if ($this->settings) return $this->settings;

$result=$this->mysql->query(sprintf("SELECT `set_name`, `set_value` FROM `profs2settings`
JOIN `settings` USING(`id_set`)
JOIN `tries` USING(`id_prof`)
WHERE `id_try` = '%u'", 
ID_TRY));

while($row=$result->fetch_assoc()) {
$settings[$row['set_name']]=$row['set_value'];
}

$this->settings=$settings;
return $settings;
}

protected function extract_settings(array $s_names) {
$all=$this->get_settings();
foreach ($s_names as $s_name) {
$settings[$s_name]=$all[$s_name];
}

return $settings;
}

protected function get_add_settings() {
return $this->extract_settings(array('minadd', 'maxadd'));
}

protected function get_sub_settings() {
return $this->extract_settings(array('minsub', 'maxsub', 'submin'));
}

protected function get_deadline() {
$try_start=$this->mysql->extract_value(sprintf("SELECT `try_start` FROM `tries`
JOIN `tries2sessions` USING(`id_try`)
WHERE `id_try` = '%u' AND `sid` = '%s'", 
ID_TRY, $this->mysql->escape($_SESSION['sid'])));
$available_time=$this->extract_settings(array('available_time'))['available_time'];

return $this->extract_time($try_start)+$available_time;
}

}