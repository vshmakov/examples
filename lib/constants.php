<?php

define('PRICE', 49);
define('TEST_DAYS', 3);
define('DEFAULT_MONEY', 0);
define('RECHARGE_TITLE', 'Пополнение счёта exmasters.ru пользователя ');

call_user_func(function () {
    $min = 60;
    $hour = 60 * $min;
    $day = 24 * $hour;
    $toUtc = -3 * $hour;

    foreach ([
        'MIN' => $min,
        'HOUR' => $hour,
        'DAY' => $day,
        'MONTH' => 30 * $day,
        'TO_UTC' => $toUtc,
    ] as $key => $val) {
        define($key, $val);
    }
});
