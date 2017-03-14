<?php

use React\EventLoop\Factory;
use React\SocketClient\TcpConnector;
use React\SocketClient\DnsConnector;
use React\SocketClient\TimeoutConnector;
use React\SocketClient\ConnectionInterface;

require __DIR__ . '/../vendor/autoload.php';

$loop = Factory::create();

$factory = new \React\Dns\Resolver\Factory();
$resolver = $factory->create('8.8.8.8', $loop);

$tcp = new TcpConnector($loop);
$dns = new DnsConnector($tcp, $resolver);

// time out connection attempt in 3.0s
$dns = new TimeoutConnector($dns, 3.0, $loop);

$target = isset($argv[1]) ? $argv[1] : 'www.google.com:80';

$dns->connect($target)->then(function (ConnectionInterface $connection) use ($target) {
    $connection->on('data', function ($data) {
        echo $data;
    });
    $connection->on('close', function () {
        echo '[CLOSED]' . PHP_EOL;
    });

    $connection->write("GET / HTTP/1.0\r\nHost: $target\r\n\r\n");
}, 'printf');

$loop->run();
