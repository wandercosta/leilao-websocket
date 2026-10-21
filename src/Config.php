<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

/**
 * Lê a configuração do ambiente. Em Docker as variáveis já chegam pelo
 * docker-compose; fora dele, são lidas do arquivo .env na raiz do projeto.
 */
final class Config
{
    private static bool $carregado = false;

    public static function carregar(string $raiz): void
    {
        if (self::$carregado) {
            return;
        }

        Dotenv::createImmutable($raiz)->safeLoad();
        self::$carregado = true;
    }

    public static function get(string $chave, ?string $padrao = null): string
    {
        $valor = $_ENV[$chave] ?? $_SERVER[$chave] ?? getenv($chave);

        if ($valor === false || $valor === null || $valor === '') {
            if ($padrao === null) {
                throw new \RuntimeException("Variável de ambiente {$chave} não definida. Veja o .env.example.");
            }
            return $padrao;
        }

        return (string) $valor;
    }
}
