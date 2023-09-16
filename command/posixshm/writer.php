<?php

declare(strict_types=1);

require_once __DIR__ . '/shMTools.php';

try {
    $str = 'Hella';
    $key = ftok(__FILE__, 'd');

    $shm = reserveShm($key, strlen($str));
    $ln = writeShm($shm, $str);
} catch (Throwable $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}

echo $ln . PHP_EOL;
exit(0);
