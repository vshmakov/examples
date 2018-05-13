<?php
class m_users extends users_main {	
private static $instance;	
protected $mysql;				
protected $m_site_pages;
protected $m_rights;
public static function get_instance() {
		if (!self::$instance ) self::$instance = new self();
			return self::$instance;
	}

private function __construct() {
		$this->mysql = m_mysql::get_instance();
$this->m_site_pages=m_site_pages::get_instance();
$this->m_rights=m_rights::get_instance();
}

}