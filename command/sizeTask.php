<?php

declare(strict_types=1);

$size = 0;
$sizeVarKey = 1;
$sizeStep = 15;
$needSize = 20;
$forkCnt = 10;

$checkSizeEvent = new SyncEvent(name: 'checkSizeEvent', manual: 1);
$sizeMtx = new SyncMutex(name: 'sizeMtx');
$shm = shm_attach(
    key: ftok(__FILE__, 't'),
    size: 100,
    permissions: 0644
);

shm_put_var(shm: $shm, key: $sizeVarKey, value: $size);
echo "Start size: $size l" . PHP_EOL;

for ($j = 0; $j < $forkCnt; $j++) {
    if (!isset($pid) || $pid > 0) {
        $pid = pcntl_fork();
    } else {
        break;
    }
}

if ($pid > 0) {
    $myPid = getmypid();

    for ($i = 0; $i < 10; $i++) {
        $sizeMtx->lock();

        $size = shm_get_var(shm: $shm, key: 1);
        $size += $sizeStep;
        echo "#P($myPid) Add $sizeStep size" . PHP_EOL;
        echo "#P($myPid) New size: $size l" . PHP_EOL;
        shm_put_var(shm: $shm, key: $sizeVarKey, value: $size);

        $sizeMtx->unlock();

        echo "#P($myPid) Fire" . PHP_EOL;
        $checkSizeEvent->fire();
        usleep(10000);
    }

    pcntl_wait(status: $status);
    shm_remove($shm);
} else {
    $myPid = getmypid();
    $sizeMtx->lock();

    while (true) {
        echo "#C($j) Start check" . PHP_EOL;
        $size = shm_get_var(shm: $shm, key: $sizeVarKey);
        if ($size >= $needSize) {
            echo "#C($j) $size > $needSize" . PHP_EOL;
            break;
        }
        $sizeMtx->unlock();
        echo "#C($j) Start wait" . PHP_EOL;
        $res = $checkSizeEvent->wait();
        if (!$res) {
            echo "#C($j) Can`t get size" . PHP_EOL;
            exit($j);
        }
        $checkSizeEvent->reset();
        echo "#C($j) Get signal" . PHP_EOL;
        $sizeMtx->lock();
    }
    $size -= $needSize;
    echo "#C($j) Get $needSize l" . PHP_EOL;
    shm_put_var(shm: $shm, key: $sizeVarKey, value: $size);
    echo "#C($j) Now size $size" . PHP_EOL;

    $sizeMtx->unlock();
}
