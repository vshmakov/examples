<?php
abstract class users_main extends users_base {
	protected $user;
public $errors=[];

public function check_rights($con=CONTROLLER, $act=ACTION) {
$page=$this->m_site_pages->get_page($con, $act);
if (!$page) {
$this->errors[]='such_page_does_not_exist';
return false;
}
$this->check_session();
$user=$this->get_user();
$this->fill_session();
return $this->Can($page['priv_name'], $user['id_user']);
}

protected function check_session() {
$key=(isset($_SESSION['last_time']) && (time()-$_SESSION['last_time'])<=(self::S_LONG) && isset($_SESSION['sid']) && 
isset($_SESSION['user']) && ($_SESSION['user']) && isset($_SESSION['user_privs'])); 
if (!$key) {
$this->errors[]='invalid_session';
$this->kill_session();
$this->fill_session();
} 
return $key;
}

public function Can($priv) {
$this->fill_session();
$can=(isset($_SESSION['user_privs']) && is_array($_SESSION['user_privs'])) ? in_array($priv, $_SESSION['user_privs']) : false;
if (!$can) $this->errors[]='insufficient_rights';
return $can;
	}

protected function get_user() {
if ($this->user) return $this->user;

$uid=$this->Get_uid();
if (!$uid) {
$this->errors[]='the_user_is_not_authorized';
return $this->get_by_login('__GUEST');
}
$user=$this->mysql->p_query(sprintf("SELECT * FROM `users`
JOIN `roles` USING(`id_role`)
WHERE `id_user` ='%u'", $uid));
$user=(isset($user[0])) ? $user[0] : null;
$this->user=$user;
return $user;
}

	public function get_by_login($login) {	
		$user=$this->mysql->p_query(sprintf("SELECT * FROM `users`
JOIN `roles` USING(`id_role`)
WHERE `login` = '%s'", $this->mysql->escape($login)));
if (!$user) {
$this->errors[]='this_user_does_not_exist';
return false;
}
$user=(isset($user[0])) ? $user[0] : null;
$this->user=$user;
return $user;
	}
			
protected  function fill_session() {
$_SESSION['last_time']=time();
if (!isset($_SESSION['user']) || !$_SESSION['user']) {
$user=$this->get_user();
$_SESSION['user']=$user;
$_SESSION['user_privs']=$this->m_rights->get_privs_by_role_name($user['role_name']);
}
if (!isset($_SESSION['sid']) || !$_SESSION['sid']) $_SESSION['sid'] = $this->get_sid();				
setcookie('sid', $this->get_sid(), time()+self::S_LONG);
return true;
}

}