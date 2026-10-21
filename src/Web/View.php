<?php

declare(strict_types=1);

namespace App\Web;

final class View
{
    /** @param array<string, mixed> $dados */
    public static function render(string $template, array $dados = []): void
    {
        extract($dados, EXTR_SKIP);
        $e = static fn (mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

        ob_start();
        require dirname(__DIR__, 2) . "/templates/{$template}.php";
        $conteudo = ob_get_clean();

        require dirname(__DIR__, 2) . '/templates/layout.php';
    }
}
