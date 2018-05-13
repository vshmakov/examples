<?php
class m_site_pages extends sp_main {	
private static $instance;	
	protected  $mysql;				
protected $m_rights;

public static function get_instance() {
		if (self::$instance == null) self::$instance = new self();
			return self::$instance;
	}

	private function __construct() {
		$this->mysql = m_mysql::get_instance();
$this->m_rights=m_rights::get_instance();
}

}