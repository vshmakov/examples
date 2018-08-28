<?php

use App\DT;

function show(...$vars)
{
    dump(implode(' - ', $vars));
}

function dt($dt)
{
    return DT::createFromDT($dt);
}

function normPerc($p)
{
    $all1 = 0;

    foreach ($p as $k => $v) {
        $all1 += abs($v);
    }

    if (!$all1) {
        $all1 = 1;
    }
    $all2 = 0;

    foreach ($p as $key => $val) {
        $all2 += $p[$key] = round($val / $all1 * 100);
    }

    foreach (array_reverse($p) as $k => $v) {
        if ($v) {
            $p[$k] += 100 - $all2;

            return $p;
        }
    }

    $p[$k] += 100 - $all2;

    return $p;
}

function getArrByKeies($arr, $ka)
{
    $res = [];

    foreach ($ka as $k) {
        if (isset($arr[$k])) {
            $res[$k] = $arr[$k];
        }
    }

    return $res;
}

function getArrByStr($s)
{
    $a = explode(' ', $s);

    return $a;
}

function getMethodName($s, $p = '')
{
    $s = getVarName($s);

    if ($p) {
        $s = ucfirst($s);
    }

    return $p.$s;
}

function entityGetter($v)
{
    return preg_match('#^get[A-Z]#', $v) ? $v : 'get'.ucfirst($v);
}

function getKeysFromEntity($s, $e)
{
    $d = [];

    foreach ((getArrByStr($s)) as $k) {
        $m = entityGetter($k);
        $d[$k] = $e->$m();
    }

    return $d;
}

function _log(...$attr)
{
    file_put_contents(__DIR__.'/log.log', json_encode($attr));
}

function createNumArr($a)
{
    $rn = [];

    foreach ($a as $v) {
        $rn[] = $v;
    }

    return $rn;
}

function minVal($k, $v)
{
    return $v >= $k ? $v : $k;
}

function maxVal($k, $v)
{
    return $v <= $k ? $v : $k;
}

function btwVal($min, $max, $v, $k = null)
{
    if (null === $k) {
        return maxVal($max, minVal($min, $v));
    }

    $out = (($v < $min) or ($v > $max));

    if ($k) {
        return $out ? $max : $v;
    } else {
        return ($out) ? $min : $v;
    }
}

function makeVarKeys($a, $s = 'x')
{
    foreach ($a as $k => $v) {
        $a[$s.$k] = $v;
    }

    return $a;
}

function getVarName($s)
{
    $a = explode('_', strtolower($s));
    $v = array_shift($a);

    foreach ($a as $t) {
        $v .= ucfirst($t);
    }

    return $v;
}

function distPerc(float $v, float $f, float $t)
{
    $o = ($t - $f) / 2;
    $d = ($o - $v);
    $r = round(abs($d) / abs($o) * 100);

    return $r;
}

function prob($p)
{
    return mt_rand(1, 100) <= $p;
}

function randStr($length = 32)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789';
    $code = '';
    $clen = strlen($chars) - 1;

    while (strlen($code) < $length) {
        $code .= $chars[mt_rand(0, $clen)];
    }

    return $code;
}

function hugeNumStyle(int $n)
{
    $rev = function (string $s) {
        $s1 = '';

        for ($i = strlen($s) - 1; $i >= 0; --$i) {
            $s1 .= $s[$i];
        }

        return $s1;
    };

    $s = $rev($n);
    $s1 = '';
    $to = strlen($s) - 1;

    for ($i = 0; $i <= $to; ++$i) {
        $s1 .= $s[$i];

        if ($i != $to && 0 == (($i + 1) % 3)) {
            $s1 .= '.';
        }
    }

    return $rev($s1);
}
