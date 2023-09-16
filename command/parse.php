<?php

declare(strict_types=1);

$ch = curl_init('https://grpc.io/');
if ($ch === false) {
    throw new RuntimeException('curl_init() error');
}

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

//curl_setopt($ch, CURLOPT_HEADER, []);
curl_setopt($ch, CURLOPT_REFERER, 'https://www.google.com/');
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/114.0');

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

curl_setopt($ch, CURLOPT_VERBOSE, true);

//curl_setopt($ch, CURLOPT_PROXY, '172.19.0.1:8001');
curl_setopt($ch, CURLOPT_PROXY, '20.210.113.32:8123');
//curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTPS);

$res = curl_exec($ch);
print $res;

curl_close($ch);
