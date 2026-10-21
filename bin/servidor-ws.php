<?php

declare(strict_types=1);

use App\Auth\TokenDeSala;
use App\Config;
use App\Database;
use App\Leilao\LeilaoRepositorio;
use App\WebSocket\SalaDeLeilao;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require dirname(__DIR__) . '/vendor/autoload.php';

Config::carregar(dirname(__DIR__));

// Processo de longa duração: reabre a conexão com o MySQL se ela cair.
$pdo = null;
$repositorio = function () use (&$pdo): LeilaoRepositorio {
    try {
        $pdo?->query('SELECT 1');
    } catch (\PDOException) {
        $pdo = null;
    }
    $pdo ??= Database::conectar();

    return new LeilaoRepositorio($pdo);
};

$sala = new SalaDeLeilao($repositorio, new TokenDeSala(Config::get('APP_SECRET')));
$porta = (int) Config::get('WS_PORT', '8090');

$servidor = IoServer::factory(new HttpServer(new WsServer($sala)), $porta);
$servidor->loop->addPeriodicTimer(1, fn () => $sala->verificarEncerramentos());

echo "Servidor WebSocket ouvindo na porta {$porta}\n";
$servidor->run();
