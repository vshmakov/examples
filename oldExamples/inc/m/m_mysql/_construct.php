<?php
class m_mysql extends mysql_main {
private static $instance;
    protected $link;    

private $hostname = 'localhost';
private $username = 'root';
    private $password = '';
private $dbName   = 'examples';
   
public static function get_instance() {
    	if (is_null(self::$instance)) self::$instance = new self();
    	return self::$instance;
    }

	private function __construct() {
$this->link=new mysqli($this->hostname, $this->username, $this->password, $this->dbName);
$this->query('SET NAMES utf8');
	    $this->set_charset('utf8');
	    }

		public function __get($name) {
return $this->link->$name;
}

public function __call($name, $params) {
foreach($params as $key=>$value) {
$arr['x'.$key]=$value;
}
$result=null;
extract($arr);
switch (count($arr)) {
case 0:
$result=$this->link->$name();
break;
case 1:
$result=$this->link->$name($x0);
break;
case 2:
$result=$this->link->$name($x0, $x1);
break;
case 3:
$result=$this->link->$name($x0, $x1, $x2);
break;
case 4:
$result=$this->link->$name($x0, $x1, $x2, $x3);
break;
case 5:
$result=$this->link->$name($x0, $x1, $x2, $x3, $x4);
break;
case 6:
$result=$this->link->$name($x0, $x1, $x2, $x3, $x4, $x5);
break;
}
return $result;
}

}