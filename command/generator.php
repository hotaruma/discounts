<?php

declare(strict_types=1);

use React\EventLoop\Loop;

require_once __DIR__ . "/../vendor/autoload.php";

//$loop = Loop::get();
//
//$loop->addPeriodicTimer(1, function () {
//    echo 'Hi from callback' . PHP_EOL;
//});
//
//$loop->run();

function makeHex($st): string
{
    $hex = [];
    for ($i = 0; $i < strlen($st); $i++) {
        $hex[] = sprintf("%02X", ord($st[$i]));
    }
    return join(" ", $hex);
}

//$fileName = 'file://' . __DIR__ . '/text.txt';
//$file = fopen(filename: $fileName, mode: 'rt');
//$file = fopen(filename: 'https://loripsum.net/api/10/short/headers', mode: 'rb');
//
//var_dump($file);
//while (true) {
//    $st = fgets($file);
//    if ($st === false) {
//        break;
//    }
////    echo makeHex() . PHP_EOL;
//    echo $st;
//}
//
//fclose($file);

//$req = eio_rename(path: __DIR__ . '/newText.txt', new_path: __DIR__ . '/text.txt', callback: function () {
//    echo 'Hi' . PHP_EOL;
//});
//eio_cancel($req);
//eio_event_loop();

//`sleep 10`;
//passthru('sleep 10');
//echo '123';

//$command = "ls -l";
//$handle = popen($command, 'r');
//
//if ($handle) {
//    while (!feof($handle)) {
//        echo fread($handle, 4096);
//    }
//    fclose($handle);
//}
