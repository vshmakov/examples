<?php

function stop($var='', $key=0, $times=1) {
static $doneTimes=0;

$funcs=['json', 'dump', 'print_r', 'var_dump', 'json_encode'];
if (++$doneTimes>=$times) die($funcs[$key]($var));
}

function isInt($var) {
return strlen((int) $var)===strlen($var);
}

function isNat($var) {
return isInt($var) && $var>=1;
}

function nat($var) {
return abs((int) $var);
}

function esc($str) {
return get_instance()->db->escape($str);
}

function json($var) {
$json=str_replace('}', '<br>}<br>',
str_replace('{', '{<br>',
str_replace(':', ' : ',
str_replace(',', ',<br>',
json_encode($var)))));
$json=preg_replace('#(<br>)+#u', '<br>', $json);
echo'<hr>'.$json;
}

function login() {
return l_login::getInstance();
}

function cookie($key, $else=null) {
return get_instance()->input->cookie($key, $else);
}

function session($key, $else=null) {
return (isset($_SESSION[$key])) ? $_SESSION[$key] : $else;
}

function randStr($length) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
		$code = "";
		$clen = strlen($chars) - 1;

		while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $clen)];
}

		return $code;
	}

	function debug($message, $block=null) {
if (!file_exists($filePath=__dir__.'/log.txt')) file_put_contents($filePath, '');
$desc=fopen($filePath, 'a');
$text=json_encode($message)."\r\n";
if ($block) $text="$block - $text";
fwrite($desc, $text);
fclose($desc);
return $message;
	}

	function _db() {
	return get_instance()->db;
	}

	function compare($a, $b, $sign) {
	switch ($sign) {
	case '=':
	return $a==$b;
	break;
	case '!=':
	return $a!=$b;
	break;
	case '<':
	return $a<$b;
	break;
	case '>':
	return $a>$b;
	break;
	case '<=':
	return $a<=$b;
	break;
	case '>=':
	return $a>=$b;
	break;
	}
	}

	function hr($text='') {
	echo "<hr>$text<hr>";
	}

    function param($key, $else=null) {
        return (isset(get_instance()->params[--$key])) ? get_instance()->params[$key] : $else;
    }

    function isPost() {
        return !empty($_POST);
    }

    function isFiles() {
        return !empty($_FILES);
    }

    function post($key, $else=null) {
        return (isset($_POST[$key])) ? $_POST[$key] : $else;
    }

    function redirect($path) {
        die(header("location: $path"));
    }

function get($key, $else=null) {
return (isset($_GET[$key])) ? $_GET[$key] : $else;
}

function isGet() {
return !empty($_GET);
}

function getEntityManager() {
return getInstance()->getDoctrine()->getManager();
}

function getInstance() {
return \AppBundle\Controller\MainController::$instance;
}

function em() {
return getEntityManager();
}

function a($alias) {
$entities=[];

foreach ([
'u'=>'Users',
't'=>'Tries',
'e'=>'Examples',
's'=>'Sessions',
'p'=>'Profiles',
] as $key=>$val) {
$entities[$key]="\\AppBundle\\Entity\\$val";
}

return $entities[$alias] ?? $alias;
}

function er($alias) {
$c=a($alias);
return em()->getRepository($c);
}

function request() {
static $r;
return $r ?? $r=\Symfony\Component\HttpFoundation\Request::createFromGlobals();
}

function createQuery($query, $args=[]) {
foreach($args as $key=>$alias) {
$args[$key]=a($alias);
}

return em()->createQuery(call_user_func_array('sprintf', array_merge([$query], $args)));
}

function throwNotFoundExseption() {
return throwNotFoundException(); 
}

function throwNotFoundException() {
throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
}

function throwAccessDeniedException($message=null) {
throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException($message);
}