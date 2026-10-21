<?php

declare(strict_types=1);

namespace App\Leilao;

final class Dinheiro
{
    public static function formatar(int $centavos): string
    {
        return 'R$ ' . number_format($centavos / 100, 2, ',', '.');
    }

    /** Converte o que o usuário digitou ("1.250,50", "1250.5", 1250) em centavos. */
    public static function paraCentavos(string|int|float $valor): ?int
    {
        if (is_int($valor) || is_float($valor)) {
            return $valor > 0 ? (int) round($valor * 100) : null;
        }

        $texto = trim(str_replace(['R$', ' '], '', $valor));

        if (str_contains($texto, ',')) {
            $texto = str_replace(['.', ','], ['', '.'], $texto);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $texto)) {
            // "1.250" no formato brasileiro é mil duzentos e cinquenta
            $texto = str_replace('.', '', $texto);
        }

        if (!is_numeric($texto) || (float) $texto <= 0) {
            return null;
        }

        return (int) round((float) $texto * 100);
    }
}
