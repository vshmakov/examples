<?php

$app = [
    'PRICE' => 49,
    'TEST_DAYS' => 3,
    'DEFAULT_MONEY' => 0,
    'RECHARGE_TITLE' => 'Пополнение счёта exmasters.ru пользователя ',
];

    $min = 60;
    $hour = 60 * $min;
    $day = 24 * $hour;
    $toUtc = -3 * $hour;

    foreach ($app + [
        'MIN' => $min,
        'HOUR' => $hour,
        'DAY' => $day,
        'MONTH' => 30 * $day,
        'TO_UTC' => $toUtc,
    ] as $key => $val) {
        define($key, $val);
    }
