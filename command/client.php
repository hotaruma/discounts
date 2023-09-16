<?php

declare(strict_types=1);

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($sock === false) {
    throw new RuntimeException("Не удалось выполнить socket_create(): причина: " . socket_strerror(socket_last_error()));
}

try {
    $result = socket_connect($sock, '127.0.0.1', 10000);
    if ($result === false) {
        throw new RuntimeException("Не удалось выполнить socket_connect().\nПричина: " . socket_strerror(socket_last_error($sock)));
    }
    socket_set_nonblock($sock);
    stream_set_blocking(STDIN, false);

    while (true) {
        usleep(1000);

        $sRead = [$sock];
        $sWrite = [];
        $sExcept = [];

        $res = socket_select($sRead, $sWrite, $sExcept, 0);
        if ($res === false) {
            throw new RuntimeException("Не удалось выполнить socket_select()");
        }

        if (in_array($sock, $sRead)) {
            $out = socket_read($sock, 2048);
            echo $out;
        }

        $strRead = [STDIN];
        $strWrite = [];
        $strExcept = [];

        $res = stream_select($strRead, $strWrite, $strExcept, 0);
        if ($res === false) {
            throw new RuntimeException("Не удалось выполнить stream_select()");
        }

        if (in_array(STDIN, $strRead)) {
            $text = fgets(STDIN);
            if ($text === false) {
                throw new RuntimeException("Не удалось прочитать сообщение");
            }
            $text = trim($text);
            echo "Отправка сообщения: $text" . PHP_EOL;

            $res = socket_write($sock, $text, strlen($text));
            if ($res === false) {
                throw new RuntimeException("Не удалось выполнить socket_write().\nПричина: " . socket_strerror(socket_last_error($sock)));
            }

            if ($text == 'die' || $text == 'dd') {
                echo "Остановка" . PHP_EOL;
                break;
            }
        }
    }
} catch (Throwable) {
}
socket_close($sock);
