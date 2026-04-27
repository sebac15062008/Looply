<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Looply\ChatServidor;

require dirname(__DIR__) . '/vendor/autoload.php';

$servidor = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServidor()
        )
    ),
    8081
);

echo "Servidor iniciado en el puerto 8081\n";
$servidor->run();
