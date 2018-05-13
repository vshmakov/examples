<?php
class m_rights extends rights_main {	
private static $instance;	
	protected  $mysql;				

public static function get_instance() {
		if (self::$instance == null) self::$instance = new self();
			return self::$instance;
	}

	private function __construct() {
		$this->mysql = m_mysql::get_instance();
}

}