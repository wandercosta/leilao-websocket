<?php

declare(strict_types=1);

use App\Auth\TokenDeSala;
use App\Config;
use App\Database;
use App\Leilao\LeilaoRepositorio;
use App\Web\View;

require dirname(__DIR__) . '/vendor/autoload.php';

Config::carregar(dirname(__DIR__));

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(16));

$caminho = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/', '/') ?: '/';
$metodo = $_SERVER['REQUEST_METHOD'];
$usuario = $_SESSION['usuario'] ?? null;

$redirecionar = function (string $para): never {
    header("Location: {$para}");
    exit;
};

// Login e logout
if ($caminho === '/login') {
    if ($usuario) {
        $redirecionar('/');
    }

    $erro = null;
    $email = '';

    if ($metodo === 'POST') {
        $email = trim((string) ($_POST['email'] ?? ''));

        if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
            $erro = 'Sessão expirada, tente novamente.';
        } else {
            $stmt = Database::conectar()->prepare('SELECT id, nome, senha_hash FROM usuarios WHERE email = ?');
            $stmt->execute([$email]);
            $encontrado = $stmt->fetch();

            if ($encontrado && password_verify((string) ($_POST['senha'] ?? ''), $encontrado['senha_hash'])) {
                session_regenerate_id(true);
                $_SESSION['usuario'] = ['id' => (int) $encontrado['id'], 'nome' => $encontrado['nome']];
                $redirecionar('/');
            }
            $erro = 'E-mail ou senha inválidos.';
        }
    }

    View::render('login', ['erro' => $erro, 'email' => $email, 'csrf' => $_SESSION['csrf']]);
    exit;
}

if ($caminho === '/logout' && $metodo === 'POST') {
    if (hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        session_destroy();
    }
    $redirecionar('/login');
}

// Daqui pra baixo, só logado
if (!$usuario) {
    $redirecionar('/login');
}

$repositorio = new LeilaoRepositorio(Database::conectar());

if ($caminho === '/') {
    View::render('lista', [
        'usuario' => $usuario,
        'csrf' => $_SESSION['csrf'],
        'leiloes' => $repositorio->listar(),
    ]);
    exit;
}

if (preg_match('#^/leilao/(\d+)$#', $caminho, $m) && ($leilao = $repositorio->buscar((int) $m[1]))) {
    $tokens = new TokenDeSala(Config::get('APP_SECRET'));

    View::render('sala', [
        'usuario' => $usuario,
        'csrf' => $_SESSION['csrf'],
        'leilao' => $leilao,
        'token' => $tokens->emitir($usuario['id'], $usuario['nome']),
        'wsUrl' => Config::get('WS_PUBLIC_URL', 'ws://localhost:8090'),
    ]);
    exit;
}

http_response_code(404);
View::render('404', ['usuario' => $usuario, 'csrf' => $_SESSION['csrf']]);
