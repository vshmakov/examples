<?php
abstract class users_lip {
const S_LONG=20*60;
protected $sid;				

protected function get_sid() {
		if ($this->sid) return $this->sid;
		
		$sid =(isset($_SESSION['sid']) && $_SESSION['sid']) ? $_SESSION['sid'] : null;
		if (!$sid && isset($_COOKIE['sid'])) $sid=$_COOKIE['sid'];
		
if ($sid) {
						$session['time_last'] = date('Y-m-d H:i:s', time()+1);
			$where = sprintf("`sid` = '%s'", $this->mysql->escape($sid));
			$affected_rows = $this->mysql->Update('sessions', $session, $where);
	
if (!$affected_rows ) {
$count=$this->mysql->get_count('sessions', sprintf("`sid` = '%s'", $this->mysql->escape($sid)));
				
				if (!$count) {
$sid = null;
$this->kill_session();
}
			}			
		}		
		
if (!$sid  && isset($_COOKIE['login'])) {
$login=$_COOKIE['login'];
$user = $this->get_by_login($login);

$password=$_COOKIE['password'];
			if ($user  && $user['password'] == $password) {
			$sid = $this->OpenSession($user['id_user']);
			}
		}
		
				if (!$sid && isset($_SESSION['user']) && $_SESSION['user']['login']=='__GUEST') $sid=$this->OpenSession($_SESSION['user']['id_user']);
		if ($sid ) $this->sid = $sid;
return $sid;		
	}
	
	public function Get_uid() {	
		if ($this->user['id_user']) return $this->user['id_user'];

		$sid = $this->get_sid();
if (!$sid ) return null;
			
				$id_user= $this->mysql->get_value('sessions', 'sid', $this->mysql->escape($sid), 'id_user'); 
		return ($id_user)   ? $id_user : null;
	}

protected function OpenSession($id_user) {
		$sid = $this->GenerateStr();
				
		$now = date('Y-m-d H:i:s'); 
		$session = [];
		$session['id_user'] = $id_user;;
		$session['sid'] = $sid;
		$session['time_start'] = $now;
		$session['time_last'] = $now;				
$this->mysql->Insert('sessions', $session); 
return $sid;	
	}

protected  function GenerateStr($length = 15) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
		$code = "";
		$clen = strlen($chars) - 1;  

		while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $clen)];  
}

		return $code;
	}

}