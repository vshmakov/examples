<?php
abstract class sp_rights extends sp_examples {
public function make_users2roles() {
$users=$this->m_rights->get_users();
$roles=$this->m_rights->get_roles();
$countrows= (count($users)>count($roles)) ? count($users) : count($roles);
for ($i=0; $i<$countrows; $i++) {
$arr=[];
if (isset($users[$i])) {
$arr['id_user']=$users[$i]['id_user'];
$arr['user_name']=$users[$i]['user_name'];
} else $arr['id_user']=$arr['user_name']=null;
if (isset($roles[$i])) {
$arr['id_role']=$roles[$i]['id_role'];
$arr['role_name']=$roles[$i]['role_name'];
} else $arr['id_role']=$arr['role_name']=null;

$rows[$i]=$arr;
}
return $rows;
}

public function make_privs2roles() {
$roles=$this->m_rights->get_roles(true);
$privs=$this->m_rights->get_privs();
$countrows= (count($privs)>count($roles)) ? count($privs) : count($roles);
for ($i=0; $i<$countrows; $i++) {
$arr=[];
if (isset($privs[$i])) {
$arr['id_priv']=$privs[$i]['id_priv'];
$arr['priv_name']=$privs[$i]['priv_name'];
} else $arr['id_priv']=$arr['priv_name']=null;
if (isset($roles[$i])) {
$arr['id_role']=$roles[$i]['id_role'];
$arr['role_name']=$roles[$i]['role_name'];
} else $arr['id_role']=$arr['role_name']=null;

$rows[$i]=$arr;
}
return $rows;
}

public function get_controllers() {
$site_info=$this->get_site_info();
$result=$this->mysql->query(sprintf("SELECT `id_page` AS `id_con`, `title` AS `con_name` FROM `site_pages`
WHERE `id_con` = '%u'
ORDER BY `title` ASC", 
$site_info['id_page']));
$controllers[0]=array('id_con'=>$site_info['id_page'], 'con_name'=>$site_info['title']);
while ($row=$result->fetch_assoc()) {
$controllers[]=$row;
}
return $controllers;
}

public function get_privs() {
return m_rights::get_instance()->get_privs();
}

}