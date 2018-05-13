<?php
class c_auth extends c_base {
private $m_users;

protected function before() {
parent::before();
$this->m_users = m_users::get_instance();
$this->m_users->logout();
}

protected function action_index() {
if ($this->is_post()) {
if ($this->m_users->auth($_POST['login'], $_POST['password'], isset($_POST['remember']))) die(header('Location: /'));
}
$this->content=$this->make_page();
}

protected function action_registr() {
if ($this->is_post()) {
if ($this->m_users->registr($_POST['login'], $_POST['name'], $_POST['password1'], $_POST['password2'], isset($_POST['remember']))) die(header('Location: /'));
}
$this->content=$this->make_page();
}

protected function check_rights() { }
}