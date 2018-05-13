<?php
abstract class rights_main {
public function get_privs_by_role_name($role_name) {
$result=$this->mysql->query(sprintf("SELECT `priv_name` FROM `privs`
JOIN `privs2roles` USING(`id_priv`)
JOIN `roles` USING(`id_role`)
WHERE `role_name` = '%s'", $this->mysql->escape($role_name)));
$privs=[];
while ($row=$result->fetch_assoc()) {
$privs[]=$row['priv_name'];
}
return $privs;
}

public function get_users() {
return $this->mysql->p_query("SELECT `id_user`, `user_name`, `id_role`, `roles`.`role_name` FROM `users` 
JOIN `roles` USING(`id_role`)
WHERE `login` <> '__guest'
ORDER BY `user_name` ASC");
}

public function get_c_user() {
$c_user=$this->mysql->p_query("SELECT `id_user` FROM `users` ORDER BY `user_name` LIMIT 0, 1");
return (isset($c_user[0])) ? (int) $c_user[0]['id_user'] : false;
}

public function get_roles2user($c_user) {
$result=$this->mysql->query(sprintf("SELECT `id_role` FROM `roles` 
JOIN `users` USING(`id_role`)
WHERE `id_user` = '%u'", $c_user));
$id_role=($row=$result->fetch_assoc()) ? $row['id_role'] : false;
return $id_role;
}

public function get_roles($key=false) {
$query="SELECT `id_role`, `role_name` FROM `roles` ";
if (!$key) $query.="WHERE `role_name` <> '__guest'";
$query.="ORDER BY `role_name` ASC";
return $this->mysql->p_query($query);
}

public function change_roles($id_user, $id_role) {
return (boolean) $this->mysql->update('users', array('id_role'=>(int) $id_role), sprintf("`id_user` = '%u'", $id_user));
}

public function get_privs2role($c_role) {
$result=$this->mysql->query(sprintf("SELECT `id_priv` FROM `privs`
JOIN `privs2roles` USING(`id_priv`)
JOIN `roles` USING(`id_role`)
WHERE `id_role` = '%u'", $c_role));
$privs=[];
while ($row=$result->fetch_assoc()) {
$privs[]=$row['id_priv'];
}
return $privs;
}

public function get_privs() {
return $this->mysql->p_query("SELECT `id_priv`, `priv_name` FROM `privs`
ORDER BY `priv_name` ASC");
}

public function get_c_role() {
$c_role=$this->mysql->p_query("SELECT `id_role` FROM `roles` ORDER BY `role_name` LIMIT 0, 1");
return (isset($c_role[0])) ? (int) $c_role[0]['id_role'] : false;
}
public function change_privs($id_role, array $privs) {
$this->mysql->delete('privs2roles', sprintf("`id_role` = '%u'", $id_role));
for($i=0; $i<count($privs); $i++) {
$this->mysql->insert('privs2roles', array('id_role'=>$id_role, 'id_priv'=>$privs[$i]));
}
return true;
}

public function add_page($title, $page_name, $id_con, $id_priv) {
if (strlen((int) $id_con)!==strlen($id_con) || strlen((int) $id_priv)!==strlen($id_priv)) return false;
return ((boolean) $this->mysql->insert('site_pages', 
array('title'=>$title, 'page_name'=>$page_name, 'id_con'=>$id_con, 'id_priv'=>$id_priv)));
}

public function get_page_by_id($id_page) {
return $this->mysql->get_row('site_pages', sprintf("`id_page` = '%u'", $id_page));
}

public function edit_page($id_page, $title, $page_name, $id_con, $id_priv) {
return (boolean) $this->mysql->update('site_pages', 
array('title'=>$title, 'page_name'=>$page_name, 'id_con'=>$id_con, 'id_priv'=>$id_priv), 
sprintf("`id_page` = '%u'", $id_page));
}

public function delete_page($id_page) {
if (strlen((int) $id_page)!==strlen($id_page)) return false;
return ((boolean) $this->mysql->delete('site_pages',
sprintf("`id_page` = '%u'", $id_page)));
}

public function add_role($role_name) {
return ((boolean) $this->mysql->insert('roles', array('role_name'=>$role_name)));
}

public function add_priv($priv_name) {
return ((boolean) $this->mysql->insert('privs', array('priv_name'=>$priv_name)));
}

public function get_role($id_role) {
return $this->mysql->get_row('roles', sprintf("`id_role` = '%u'", $id_role));
}

public function edit_role($id_role, $role_name) {
return (boolean) $this->mysql->update('roles', array('role_name'=>$role_name), sprintf("`id_role` = '%u'", $id_role));
}

public function delete_role($id_role) {
$this->mysql->delete('privs2roles', sprintf("`id_role` = '%u'", $id_role));
return (boolean) $this->mysql->delete('roles', sprintf("`id_role` = '%u'", $id_role));
}

public function get_priv($id_priv) {
return $this->mysql->get_row('privs', sprintf("`id_priv` = '%u'", $id_priv));
}

public function edit_priv($id_priv, $priv_name) {
return (boolean) $this->mysql->update('privs', array('priv_name'=>$priv_name), sprintf("`id_priv` = '%u'", $id_priv));
}

public function delete_priv($id_priv) {
$this->mysql->delete('privs2roles', sprintf("`id_priv` = '%u'", $id_priv));
return (boolean) $this->mysql->delete('privs', sprintf("`id_priv` = '%u'", $id_priv));
}

public function get_user($id_user) {
return $this->mysql->get_row('users', sprintf("`id_user` = '%u'", $id_user));
}

public function edit_user($id_user, $login, $user_name) {
return (boolean) $this->mysql->update('users', array('login'=>$login, 'user_name'=>$user_name), sprintf("`id_user` = '%u'", $id_user));
}

public function delete_user($id_user) {
$this->mysql->delete('sessions', sprintf("`id_user` = '%u'", $id_user));
return (boolean) $this->mysql->delete('users', sprintf("`id_user` = '%u'", $id_user));
}

}