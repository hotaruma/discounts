<?php

declare(strict_types=1);

require_once __DIR__ . '/shMTools.php';

$key = ftok(__DIR__ . '/writer.php', 'd');

try {
    $shm = getShmForRead($key);
    $data = readShm($shm);
} catch (Throwable $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}

echo $data . PHP_EOL;
exit(0);
