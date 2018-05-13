<?phpabstract class c_controller {		protected abstract function render();	protected abstract function before();protected abstract function after();		public function Request($action) {		$this->before();		$this->$action();$this->after();		$this->render();	}		protected function is_get() {		return $_SERVER['REQUEST_METHOD'] == 'GET';	}		protected function is_post() {		return $_SERVER['REQUEST_METHOD'] == 'POST';	}	protected function template($file_name, $vars=[]) {if (is_array($vars)) extract($vars); 	ob_start();include "$file_name";		return ob_get_clean();		}				public function __call($name=null, $params=null){        die('Не пишите фигню в url-адресе!!!');	}public function rights_error() {die("Недостаточно прав");}protected function process_cb($key='checkbox', $arr=null) {
if (is_null($arr)) {
$arr=($this->is_post()) ? $_POST : $_GET;
}
$cb=[];
foreach($arr as $k=>$v) {
if (strpos($k, $key)===0) $cb[]=$v;
}
return $cb;
}
}