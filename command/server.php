<?php

declare(strict_types=1);

$address = '127.0.0.1';
$port = 10000;

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($sock === false) {
    throw new RuntimeException("Не удалось выполнить socket_create(): причина: " . socket_strerror(socket_last_error()));
}

try {
    if (socket_bind($sock, $address, $port) === false) {
        throw new RuntimeException("Не удалось выполнить socket_bind(): причина: " . socket_strerror(socket_last_error($sock)));
    }

    if (socket_listen($sock, 2) === false) {
        throw new RuntimeException("Не удалось выполнить socket_listen(): причина: " . socket_strerror(socket_last_error($sock)));
    }

//    if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) {
//        throw new RuntimeException('Не могу установить опцию на сокете: ' . socket_strerror(socket_last_error()));
//    }

    $clients = [];

    while (true) {
        $read = [$sock, ...$clients];
        $write = [];
        $except = [...$clients];

        $res = socket_select($read, $write, $except, null);
        if ($res === false) {
            throw new RuntimeException("Не удалось выполнить socket_select()");
        }

        echo "Получили триггер socket_select()" . PHP_EOL;
        echo "read " . count($read) . PHP_EOL;
        echo "write " . count($write) . PHP_EOL;
        echo "except " . count($except) . PHP_EOL;
        if (in_array($sock, $read)) {
            $msgSock = socket_accept($sock);
            if ($msgSock === false) {
                throw new RuntimeException("Не удалось выполнить socket_accept(): причина: " . socket_strerror(socket_last_error($sock)));
            }
            echo "Новое соединение " . spl_object_id($msgSock) . PHP_EOL;
            $clients[] = $msgSock;

            $msg = "\nДобро пожаловать на тестовый сервер PHP. \n" .
                "Чтобы отключиться, наберите 'die'.\n";
            socket_write($msgSock, $msg, strlen($msg));
        }

        foreach ($read as $index => $clientSocket) {
            if (!in_array($clientSocket, $clients)) {
                continue;
            }

            echo "Чтение текста от №" . spl_object_id($clientSocket) . PHP_EOL;
            $res = socket_recv($clientSocket, $text, 2048, 0);
//            if ($text === null) {
//                throw new RuntimeException("Не удалось выполнить socket_read(): причина: " . socket_strerror(socket_last_error($clientSocket)));
//            }
            if ($text === null || $text == 'die' || $res === 0) {
                unset($clients[array_search($clientSocket, $clients)]);

                $mess = "#" . spl_object_id($clientSocket) . "close connection" . PHP_EOL;
                echo $mess;
                foreach ($clients as $socket) {
                    socket_write($socket, $mess, strlen($mess));
                }
                socket_close($clientSocket);
                continue;
            }
            if (!$text = trim($text)) {
                continue;
            }

            $mess = "#" . spl_object_id($clientSocket) . "сказал '$text'." . PHP_EOL;
            echo $mess;

            foreach ($clients as $socket) {
                socket_write($socket, $mess, strlen($mess));
            }

            if ($text == 'dd') {
                break 2;
            }
        }
    }
} catch (Throwable $e) {
    echo $e->getMessage() . PHP_EOL;
}

echo 'Socket close' . PHP_EOL;
socket_close($sock);
