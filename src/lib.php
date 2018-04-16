<?php

call_user_func(function () {
$min=60;
$hour=60*$min;
$day=24*$hour;
$toUtc=-3*$hour;

foreach(array(
'MIN'=>$min,
'HOUR'=>$hour,
'DAY'=>$day,
'MONTH'=>30*$day,
'TO_UTC'=>$toUtc,
) as $key=>$val) {
define($key, $val);
}
});

