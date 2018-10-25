<?php

function normPerc(array $percents) : array
{
    $totalPercents = 0;

    foreach ($percents as $percent) {
        $totalPercents += abs($percent);
    }

    if (!$totalPercents) {
        $totalPercents = 1;
    }

    $percentsSumm = 0;

    foreach ($percents as $key => $percent) {
        $percentsSumm += $percents[$key] = round($percent / $totalPercents * 100);
    }

    foreach (array_reverse($percents) as $key => $percent) {
        if ($percent) {
            $percents[$key] += 100 - $percentsSumm;

            return $percents;
        }
    }

    $percents[$key] += 100 - $percentsSumm;

    return $percents;
}

function arr(string $string) : array
{
    $array = explode(' ', $string);

    return $array;
}

function minVal(float $min, float $value) : float
{
    return $value >= $min ? $value : $min;
}

function maxVal(float $max, float $value) : float
{
    return $value <= $max ? $value : $max;
}

function btwVal(float $min, float $max, float $value, ? bool $switch = null) : float
{
    if (null === $switch) {
        return maxVal($max, minVal($min, $value));
    }

    $out = (($value < $min) or ($value > $max));

    if ($switch) {
        return $out ? $max : $value;
    } else {
        return ($out) ? $min : $value;
    }
}

function randStr(int $length = 32) : string
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789';
    $code = '';
    $clen = strlen($chars) - 1;

    while (strlen($code) < $length) {
        $code .= $chars[mt_rand(0, $clen)];
    }

    return $code;
}

function addTimeSorter($e1, $e2)
{
    $t1 = $e1->getAddTime()->getTimestamp();
    $t2 = $e2->getAddTime()->getTimestamp();

    return timeSorter($t1, $t2);
}

function timeSorter($t1, $t2)
{
    if ($t1 == $t2) {
        return 0;
    }

    return $t1 > $t2 ? 1 : -1;
}
