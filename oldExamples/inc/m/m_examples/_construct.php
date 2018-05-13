<?php
class m_examples extends ex_main {	
private static $instance;	
	protected  $mysql;				
	protected $dsettings=array('number'=>5, 
'add'=>array('min'=>5, 'max'=>10), 
'sub'=>array('min'=>5, 'max'=>20, 'submin'=>0));

public static function get_instance() {
		if (self::$instance == null) self::$instance = new self();
			return self::$instance;
	}

	private function __construct() {
		$this->mysql = m_mysql::get_instance();
}

}