<?php

declare(strict_types=1);

namespace App\Leilao;

/**
 * Regras de negócio do lance, sem depender de banco ou de rede,
 * para poderem ser testadas isoladamente.
 *
 * Valores em centavos (int) para não ter problema de arredondamento.
 */
final class RegraDeLance
{
    /** Menor valor aceito no próximo lance. */
    public static function minimoAceito(int $lanceInicial, int $incremento, ?int $maiorLance): int
    {
        return $maiorLance === null ? $lanceInicial : $maiorLance + $incremento;
    }

    /**
     * @throws LanceRecusado
     */
    public static function validar(
        int $valor,
        int $lanceInicial,
        int $incremento,
        ?int $maiorLance,
        \DateTimeImmutable $encerraEm,
        \DateTimeImmutable $agora,
    ): void {
        if ($agora >= $encerraEm) {
            throw new LanceRecusado('Este leilão já foi encerrado e não aceita mais lances.');
        }

        $minimo = self::minimoAceito($lanceInicial, $incremento, $maiorLance);

        if ($valor < $minimo) {
            throw new LanceRecusado(sprintf(
                'Lance recusado: o valor mínimo agora é %s.',
                Dinheiro::formatar($minimo),
            ));
        }
    }
}
