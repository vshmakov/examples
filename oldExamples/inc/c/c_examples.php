<?php
class c_examples extends c_index {
protected function action_index () {
$act=(isset(PARAMS[0])) ? (int) PARAMS[0] : null;
define('ID_TRY', (int) $act);
$m_sp=m_site_pages::get_instance();

if (!$m_sp->check_try()) {
$location="location: ";

switch ($m_sp->message) {
case 'change_try':
 $location.=$m_sp->get_try_id();
 break;
default:
$location.='../history/'.$m_sp->get_try_id();
break;
}
die(header($location));
}

if (!empty($_GET)) {
m_examples::get_instance()->answer($_GET['answer']);
die(header(sprintf("location: %s", 
explode('?', $_SERVER['REQUEST_URI'])[0])));
}

$example=$m_sp->get_example();
$this->content=$this->template('inc/v/themes/examples/v_index.php', array('example'=>$example));
} 

protected function action_try () { 
$this->action_index();
}

protected function action_new() {
die(header(sprintf("location: try/%u", 
m_examples::get_instance()->open_new_try())));
}

protected function action_last() {
define('ID_TRY', m_examples::get_instance()->return_last_try());
die(header(sprintf("location: try/%u", 
m_site_pages::get_instance()->get_last_try())));
}

protected function action_history() {
$act=(isset(PARAMS[0])) ? PARAMS[0] : null;
define('ID_TRY', (int) $act);

if (strlen((int) $act)!==strlen($act) || !m_examples::get_instance()->is_such_try()) die(header("location: archive"));
 
  $hs=m_site_pages::get_instance()->get_hs();
    $history=m_site_pages::get_instance()->get_history();
$this->content=$this->make_page(array('history'=>$history)+$hs);
}

protected function action_archive() {
$archive=m_site_pages::get_instance()->get_archive();
$this->content=$this->make_page(array('archive'=>$archive));
}

protected function action_settings() {
if (!empty($_GET)) {
if (isset($_GET['current_prof'])) m_site_pages::get_instance()->set_current_prof($_GET['current_prof']);
die(header(sprintf("location: %s", 
explode('?', $_SERVER['REQUEST_URI'])[0])));
}

$profiles=m_site_pages::get_instance()->get_all_profiles();
$current_prof=m_site_pages::get_instance()->get_current_prof();
$this->content=$this->make_page(array('profiles'=>$profiles, 'current_prof'=>$current_prof));
}

protected function action_profile() {
$act=(isset(PARAMS[0]) && strlen((int) PARAMS[0])===strlen(PARAMS[0])) ? PARAMS[0] : die(header("location: settings"));
define('ID_PROF', (int) $act);
if (!m_examples::get_instance()->is_such_prof()) die(header("location: settings"));

$profile=[];
if (!empty($_GET)) {
$m_sp=m_site_pages::get_instance();
if (isset($_GET['action']) && $_GET['action']=='delete_profile' && $m_sp->delete_profile()) die(header("location: /examples/settings"));
else if (isset($_GET['checkbox']) && $_GET['checkbox']=='new' && $m_sp->create_profile($_GET)) die(header("location: /examples/settings"));
else if ($m_sp->update_profile($_GET)) die(header("location: /examples/settings"));
else $profile=$_GET;
} 

if (!$profile) $profile=m_site_pages::get_instance()->get_profile();
$this->content=$this->make_page($profile);
}

}