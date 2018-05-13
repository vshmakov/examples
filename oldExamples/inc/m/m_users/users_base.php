<?php
abstract class users_base extends users_lip {
public function registr($login, $name, $password1, $password2, $remember=true) {
$this->logout();

if ($password1!=$password2) {
$this->errors[]='passwords_do_not_match';
return false;
}

$pass=md5($password1);
$id_user=$this->mysql->insert('users', array('login'=>$login, 'password'=>$pass, 'user_name'=>$name));

if (!$id_user) {
$this->errors[]='this_login_is_already_used';
return false;
}

if ($remember) {
			$expire = time() + 3600 * 24 * 100;
			setcookie('login', $login, $expire);
			setcookie('password', $pass, $expire);
}		

$this->sid = $this->OpenSession($id_user);
$this->fill_session();
return true;
	}

	public function auth($login, $password, $remember = true) {
$this->logout();

$user = $this->get_by_login($login);

if (!$user ) {
$this->errors[]='this_user_does_not_exist';
return false;
}

$id_user = $user['id_user'];				

if ($user['password'] != md5($password)) {
$this->errors[]='wrong_password';
return false;
}
				
if ($remember) {
			$expire = time() + 3600 * 24 * 100;
			setcookie('login', $login, $expire);
			setcookie('password', md5($password), $expire);
		}		
				
				$this->sid = $this->OpenSession($id_user);
$this->fill_session();
return true;
	}
	
public function is_error($error) {
return in_array($error, $this->errors);
}

public function get_user_name() {
return $this->get_user()['user_name'];
}

public function logout() {
foreach($_COOKIE as $key=>$value) {		
setcookie($key, '', time() - 1);
		unset($_COOKIE[$key]);
}
$this->kill_session();
return true;
}

protected function kill_session() {
setcookie('sid', '', time()-1);
unset($_COOKIE['sid']);
foreach($_SESSION as $key=>$value) {
unset($_SESSION[$key]);
}
session_destroy();
session_start();
		$this->sid = null;
$this->user=null;		
return true;
}

	public function clear_sessions() {
		$min = date('Y-m-d H:i:s', time() - self::S_LONG); 			
		$where=sprintf("`time_last` < '%s'", $this->mysql->escape($min));
		$this->mysql->query("DELETE FROM `tries2sessions`
		WHERE `sid` IN 
		(SELECT `sid` FROM `sessions`
		WHERE {$where})");
		$this->mysql->delete('sessions', $where);
		return true;
	}

}