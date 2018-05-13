<?php
abstract class sp_main extends sp_rights {
const TIME_FORMAT='Y-m-d H:i:s';
protected  $site_info;
protected  $page;	
public $messages=[];

public function get_site_info() {
if ($this->site_info) return $this->site_info;

$site_info=$this->mysql->get_row('site_pages', "`title` = 'SITE' AND `id_con` IS NULL");
$this->site_info=$site_info;
return $site_info;
}

	public function get_page($con, $act) {
if ($this->page && $this->page['title']==$act && $this->page['con_name']==$con) return $this->page;

$result=$this->mysql->p_query(sprintf("SELECT `site_pages`.*, `privs`.`priv_name` FROM `site_pages` 
JOIN `privs` USING(`id_priv`) 
WHERE `title` = '%s' 
AND `id_con` IN (SELECT `id_page` FROM `site_pages` 
WHERE `title` = '%s' AND `id_con` = '%u')", 
$this->mysql->escape($act), $this->mysql->escape($con), $this->get_site_info()['id_page']));
$page= (isset($result[0])) ? $result[0] : false;
if ($page) $page['con_name']=$con;
$this->page=$page;
return $page;
}

public function get_site_name() {
return $this->get_site_info()['page_name'];
}

public function get_page_title() {
return $this->get_page(CONTROLLER, ACTION)['page_name'];
}

public function get_site_pages() {
$id_site=$this->get_site_info()['id_page'];
$arr=$this->mysql->p_query(sprintf("SELECT `site_pages`.*, `privs`.`priv_name` FROM `site_pages`
JOIN `privs` USING(`id_priv`)
WHERE `id_page` <> '%u'
ORDER BY `id_con`, `title` ASC", $id_site));
$sp[0]=$this->get_site_info();
foreach($arr as $key=>$page) {
if ($page['id_con']==$id_site) {
$sp[]=$page;
unset($arr[$key]);
}
}
for ($i=1; $i<count($sp); $i++) {
$sp[$i]['ch_pages']=[];
foreach ($arr as $key=>$page) {
if ($page['id_con']==$sp[$i]['id_page']) {
$sp[$i]['ch_pages'][]=$page;
unset($arr[$key]);;
}
}
}
return $sp;
}

public function extract_time($date_time, $format=null) {
if (!$format) $format=self::TIME_FORMAT;

return DateTime::createFromFormat($format, $date_time)->getTimestamp();
}

public function make_time($date_time, $format=null) {
if (!$format) $format=self::TIME_FORMAT;

return DateTime::createFromFormat($format, $date_time)->format('d.m.Y H:i:s');
}

}