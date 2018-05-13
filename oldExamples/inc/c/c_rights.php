<?php
class c_rights extends c_index {
protected function after() {
parent::after();
$this->menu.=$this->template('inc/v/blocks/v_rights_menu.php');
}

protected function action_index() { 
if ($this->is_post()) {
if (isset($_POST['go'])) die(header("location: {$_POST['go']}"));
m_rights::get_instance()->change_roles($_POST['id_user'], $_POST['id_role']);
}
$c_user=(isset($_GET['id_user'])) ? (int) $_GET['id_user'] : (int) m_rights::get_instance()->get_c_user();
$checked_role=m_rights::get_instance()->get_roles2user($c_user);
$rows=m_site_pages::get_instance()->make_users2roles();
$this->content=$this->make_page(array('rows'=>$rows, 'checked_role'=>$checked_role, 'current_user'=>$c_user));
} 

protected function action_privs2roles() {
if ($this->is_post()) {
if (isset($_POST['go'])) die(header("location: {$_POST['go']}"));
if ($_POST['action']=='privs2roles') {
$id_role=$_POST['id_role'];
$privs=$this->process_cb();
m_rights::get_instance()->change_privs($id_role, $privs);
}
if ($_POST['action']=='add') {
if ($_POST['type']=='role') m_rights::get_instance()->add_role($_POST['name']);
if ($_POST['type']=='priv') m_rights::get_instance()->add_priv($_POST['name']);
}
}

$c_role=(isset($_GET['id_role'])) ? (int) $_GET['id_role'] : (int) m_rights::get_instance()->get_c_role();
$checked_privs=m_rights::get_instance()->get_privs2role($c_role);
$rows=m_site_pages::get_instance()->make_privs2roles();
$this->content=$this->make_page(array('rows'=>$rows, 'current_role'=>$c_role, 'checked_privs'=>$checked_privs));
}

protected function action_site_pages() {
$m_sp=m_site_pages::get_instance();
if ($this->is_post()) {
$m_rights=m_rights::get_instance();
if ($_POST['id_con']!='no' && $_POST['id_priv']!='no') $m_rights->add_page($_POST['title'], $_POST['page_name'], $_POST['id_con'], $_POST['id_priv']);
}
$sp=$m_sp->get_site_pages();
$con=$m_sp->get_controllers();
$privs=$m_sp->get_privs();
$this->content=$this->make_page(array('site'=>array_shift($sp), 'site_pages'=>$sp, 'controllers'=>$con, 'privs'=>$privs));
}

protected function action_edit_page() {
if ($this->is_post()) {
if ($_POST['action']=='edit') m_rights::get_instance()->edit_page($_POST['id_page'], $_POST['title'], $_POST['page_name'], $_POST['id_con'], $_POST['id_priv']);
if ($_POST['action']=='delete') m_rights::get_instance()->delete_page($_POST['id_page']);
die(header("location: ../site_pages"));
} 
if ($this->is_get()) {
$m_sp=m_site_pages::get_instance();
$m_rights=m_rights::get_instance();
if (isset(PARAMS[0])) $current_page=$m_rights->get_page_by_id(PARAMS[0]);
if (!$current_page) die(header("location: ../site_pages"));
} else die(header("location: ../site_pages"));
$con=$m_sp->get_controllers();
$privs=$m_sp->get_privs();
$this->content=$this->make_page(array('current_page'=>$current_page, 'privs'=>$privs, 'controllers'=>$con));
}

protected function action_edit_role() {
if ($this->is_post()) {
if ($_POST['action']=='edit') m_rights::get_instance()->edit_role($_POST['id_role'], $_POST['role_name']);
if ($_POST['action']=='delete') m_rights::get_instance()->delete_role($_POST['id_role']);
die(header("location: ../privs2roles"));
}else if (isset(PARAMS[0]) && strlen((int) PARAMS[0])===strlen(PARAMS[0])) $current_role=m_rights::get_instance()->get_role(PARAMS[0]);
else die(header("location: ../privs2roles"));

$this->content=$this->make_page(array('current_role'=>$current_role));
}

protected function action_edit_priv() {
if ($this->is_post()) {
if ($_POST['action']=='edit') m_rights::get_instance()->edit_priv($_POST['id_priv'], $_POST['priv_name']);
if ($_POST['action']=='delete') m_rights::get_instance()->delete_priv($_POST['id_priv']);
die(header("location: ../privs2roles"));
}if (isset(PARAMS[0]) && strlen((int) PARAMS[0])===strlen(PARAMS[0])) $current_priv=m_rights::get_instance()->get_priv(PARAMS[0]);
else die(header("location: ../privs2roles"));

$this->content=$this->make_page(array('current_priv'=>$current_priv));
}

protected function action_edit_user() {
if ($this->is_post()) {
if ($_POST['action']=='edit') m_rights::get_instance()->edit_user($_POST['id_user'], $_POST['login'], $_POST['user_name']);
if ($_POST['action']=='delete') m_rights::get_instance()->delete_user($_POST['id_user']);
die(header("location: ../"));
}else if (isset(PARAMS[0]) && strlen((int) PARAMS[0])===strlen(PARAMS[0])) $current_user=m_rights::get_instance()->get_user(PARAMS[0]);
else die(header("location: ../"));

$this->content=$this->make_page(array('current_user'=>$current_user));
}

}