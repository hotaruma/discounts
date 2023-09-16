<?php

declare(strict_types=1);

require_once __DIR__ . '/shMTools.php';

try {
    $key = ftok(__DIR__ . '/writer.php', 'd');

    $shm = getShmForRead($key);
    deleteShm($shm);
} catch (Throwable $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}

exit(0);
