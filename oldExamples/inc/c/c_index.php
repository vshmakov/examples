<?php
class c_index extends c_base {
protected function after() {
parent::after();
$this->menu=$this->template('inc/v/blocks/v_menu.php', array('user_name'=>m_users::get_instance()->get_user_name()));
}

protected function action_index() { 
$this->content=$this->make_page();
}

}