<?php
abstract class sp_examples {
private $id_try;

public function check_try() {
$m_ex=m_examples::get_instance();

if ($m_ex->is_try_relevant()) return true;

if ($m_ex->is_such_try()) {
$this->message='open_history';
$this->id_try=ID_TRY;
return false;
}

if ($m_ex->return_last_try()) {
$this->message='change_try';
$this->id_try=$m_ex->return_last_try();
return false;
}

$this->message='change_try';
$this->id_try=$m_ex->open_new_try();
return false;
}

public function get_try_id() {
return $this->id_try;
}

public function get_example() {
$m_ex=m_examples::get_instance();

if ($m_ex->check_last_ex()) {
$arr=$m_ex->make_example();
$this->mysql->insert('examples', array('id_try'=>ID_TRY, 'ex_time'=>date('Y-m-d H:i:s'), 'ex_first_num'=>$arr['a'], 'opernum'=>$arr['opernum'], 'ex_second_num'=>$arr['b']));
} else $arr=$m_ex->return_last_example();

return $this->make_example_string($arr);
}

private function make_example_string($arr) {
return $arr['a'].m_examples::OPERATIONS[$arr['opernum']]['sign'].$arr['b'];
}

public function get_archive() {
$result=$this->mysql->query(sprintf("SELECT `id_try` FROM `tries`
JOIN `profiles` USING(`id_prof`)
WHERE `id_user` = '%u'
ORDER BY `id_try` DESC", 
$_SESSION['user']['id_user']));

$archive=[];
while($row=$result->fetch_assoc()) {
$arr=$this->mysql->extract_row(sprintf("SELECT min(try_start) as `time_start`, max(ex_time) as `time_finish`, count(*) as `count`  FROM `examples`
JOIN `tries` USING(`id_try`)
WHERE `id_try` = '%u'", 
$row['id_try']));
$arr['count_errors']=$this->mysql->extract_value(sprintf("SELECT count(ex_right) FROM `examples`
WHERE `ex_right` = '0' AND `id_try` = '%u'", 
$row['id_try']));
$arr['id_try']=$row['id_try'];
$arr['count']-=$arr['count_errors']-(int)
$this->mysql->extract_value(sprintf("SELECT count(*) FROM `examples`
WHERE `ex_answer` IS NULL AND `id_try` = '%u'", 
$row['id_try']));
$arr['time_start']=$this->make_time($arr['time_start']);
$arr['time_finish']=$this->make_time($arr['time_finish']);

$archive[]=$arr;
}

return $archive;
}

public function get_hs() {
$hs=$this->mysql->extract_row(sprintf("SELECT count(*) as `count`, min(try_start) as `start`, max(ex_time) as `end` FROM `examples`
JOIN `tries` USING(`id_try`)
WHERE `id_try` = '%u'", 
ID_TRY));
$hs['errors']=$this->mysql->extract_value(sprintf("SELECT count(*) FROM `examples`
WHERE `id_try` = '%u' AND `ex_right` = '0'", 
ID_TRY));

foreach (array('start', 'end') as $key) {
echo $hs[$key];
$dt=DateTime::createFromFormat('Y-m-d H:i:s', $hs[$key]);
$hs[$key]=$dt->format('d.m.Y H:i:s');
$$key=$dt->getTimestamp();
}

$hs['count']-=$hs['errors']-$this->mysql->extract_value(sprintf("SELECT count(*) FROM `examples`
WHERE `id_try` = '%u' AND `ex_answer` IS NULL", 
ID_TRY));
$hs['e_t']=(int) (($end-$start)/$hs['count']);

return $hs;
}

public function get_history() {
$result=$this->mysql->query(sprintf("SELECT `ex_first_num` as `a`, `ex_second_num` as `b`, `opernum`, `ex_answer` as `answer`, `ex_right`  FROM `examples`
WHERE `id_try` = '%u'
ORDER BY `id_ex` ASC", 
ID_TRY));

$i=1;
$history=[];
while($row=$result->fetch_assoc()) {
$arr['number']=$i;
$arr['string']=$this->make_example_string(
array('a'=>$row['a'], 'b'=>$row['b'], 'opernum'=>$row['opernum']));
$arr['answer']=(!is_null($row['answer'])) ? $row['answer'] : '-';
$arr['right']=(boolean) $row['ex_right'];

$history[]=$arr;
if ($arr['right']) $i++;
}

return array_reverse($history);
}

public function get_all_profiles() {
$result=$this->mysql->query(sprintf("SELECT `id_prof`, `prof_time`, `prof_name` FROM `profiles`
WHERE `id_user` = '%u'
ORDER BY `prof_time` ASC", 
$_SESSION['user']['id_user']));

$profiles=[];
$i=1;
while ($row=$result->fetch_assoc()) {
$arr=$row;
$arr['prof_time']=$this->make_time($arr['prof_time']);


$profiles[]=$arr;
}

return $profiles;
}

public function get_profile() {
$result=$this->mysql->query(sprintf("SELECT `set_name`, `set_value` FROM `profiles`
JOIN `profs2settings` USING(`id_prof`)
RIGHT JOIN `settings` USING(`id_set`)
WHERE `id_prof` = '%u'", 
ID_PROF));

$profile=$this->mysql->extract_row(sprintf("SELECT `prof_name` FROM `profiles`
WHERE `id_prof` = '%u'", 
ID_PROF));

while ($row=$result->fetch_assoc()) {
$profile[$row['set_name']]=$row['set_value'];
}

$profile['minutes']=(int) ($profile['available_time']/60);
$profile['seconds']=$profile['available_time']%60;

return $profile;
}

public function update_profile(array $profile) {
if (!$this->is_profile_full($profile) || !$this->is_prof_info_full($profile)) return false;

$profile=$this->update_prof_info(ID_PROF, $profile);

return $this->fill_profile(ID_PROF, $profile);
}

private function is_profile_full($profile) {
$set_names=array('addproc', 'minutes', 'seconds', 'divmin', 'divproc', 'maxadd', 'maxdiv', 'maxmult', 'maxsub', 'minadd', 'mindiv', 'minmult', 'minsub', 'multproc', 'number', 'submin', 'subproc');
foreach ($set_names as $set_name) {
if (!isset($profile[$set_name])) {
$this->messages[]='the_profile_is_not_full';
return false;
}
}

return true;
}

private function fill_profile($id_prof, $profile) {
if (!is_array($profile) || !$this->is_profile_full($profile)) return false;

extract($profile);

$number=abs((int) $number);
if ($number<3 || $number>999) {
$this->messages[]='invalid_number';
return false;
}

$profile['number']=$number;

$a_t=abs((int) $minutes)*60+abs((int) $seconds);
if ($a_t<20 || $a_t>(99*60+99)) {
$this->messages[]='invalid_time';
return false;
}

$profile['available_time']=$a_t;

$opernames=array('add', 'sub', 'mult', 'div');
$all=0;
foreach ($opernames as $opername) {
$procname=$opername.'proc';
$$procname=abs((int) $$procname);
$all+=$$procname;
}

$all2=0;
foreach ($opernames as $opername) {
$procname=$opername.'proc';
$$procname=(int) ($$procname/$all*100);
$profile[$procname]=$$procname;
$all2+=$$procname;
}
$profile['addproc']+=100-$all2;

foreach ($profile as $key=>$value) {
$is=$this->mysql->extract_value(sprintf("SELECT count(*) FROM `settings`
WHERE `set_name` = '%s'", 
$this->mysql->escape($key)));

if ($is) {
$this->mysql->query(sprintf("DELETE FROM `profs2settings`
WHERE `id_prof` = '%u' AND `id_set` IN
(SELECT `id_set` FROM `settings`
WHERE `set_name` = '%s')", 
$id_prof, $this->mysql->escape($key)));

$this->mysql->query(sprintf("INSERT INTO `profs2settings` (`id_prof`, `set_value`, `id_set`) 
VALUES ('%u', '%u', (SELECT `id_set` FROM `settings`
WHERE `set_name` = '%s'))", $id_prof, $value, $this->mysql->escape($key)));
}
}

return true;
}

public function create_profile(array $profile) {
if (!$this->is_profile_full($profile) || !$this->is_prof_info_full($profile)) return false;

$id_prof=$this->mysql->insert('profiles', array('id_user'=>$_SESSION['user']['id_user'], 
'prof_time'=>date('Y-m-d H:i:s')));

$profile=$this->update_prof_info($id_prof, $profile);

return $this->fill_profile($id_prof, $profile);
}

private function is_prof_info_full($profile) {
$is=(is_array($profile) && isset($profile['prof_name']) && strlen($profile['prof_name'])>=4);

if (!$is) {
$this->messages[]='the_profile_info_is_not_full';
}

return $is;
}

private function update_prof_info($id_prof, array $profile) {
if (!$this->is_prof_info_full($profile)) return false;

$result=$this->mysql->update('profiles', 
array('prof_name'=>$profile['prof_name']), 
sprintf("`id_prof` = '%u'", $id_prof));

unset($profile['prof_name']);

return $profile;
}

public function delete_profile() {
$this->mysql->query(sprintf("DELETE FROM `examples` 
WHERE `id_try` IN (SELECT `id_try` FROM `tries`
WHERE `id_prof` = '%u')", 
ID_PROF));
$this->mysql->delete('tries', sprintf("`id_prof` = '%u'", ID_PROF));
$this->mysql->delete('profs2settings', sprintf("`id_prof` = '%u'", ID_PROF));
return (boolean) $this->mysql->delete('profiles', sprintf("`id_prof` = '%u'", ID_PROF));
}

public function get_last_try() {
$m_ex=m_examples::get_instance();
return ($m_ex->is_try_relevant()) ? ID_TRY : $m_ex->open_new_try();
}

public function get_current_prof() {
return $this->mysql->extract_value(sprintf("SELECT `id_prof` FROM `profs2users`
WHERE `id_user` = '%u'", 
$_SESSION['user']['id_user']));
}

public function set_current_prof($id_prof) {
return (boolean) $this->mysql->query(sprintf("UPDATE `profs2users` SET `id_prof`=(SELECT `id_prof` FROM `profiles`
WHERE `id_user` = '%1\$u' AND `id_prof` = '%2\$u')
WHERE `id_user` = '%1\$u'", 
$_SESSION['user']['id_user'], $id_prof));
}

}