<?php

declare(strict_types=1);

namespace Tests;

use App\Leilao\Dinheiro;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DinheiroTest extends TestCase
{
    public static function entradas(): array
    {
        return [
            'inteiro' => [1250, 125000],
            'float' => [1250.5, 125050],
            'texto simples' => ['1250', 125000],
            'formato brasileiro' => ['1.250,50', 125050],
            'milhar sem centavos' => ['1.250', 125000],
            'com R$' => ['R$ 1.250,00', 125000],
            'ponto decimal' => ['1250.75', 125075],
            'vazio' => ['', null],
            'texto' => ['abc', null],
            'negativo' => ['-10', null],
            'zero' => [0, null],
        ];
    }

    #[Test]
    #[DataProvider('entradas')]
    public function converte_para_centavos(string|int|float $entrada, ?int $esperado): void
    {
        $this->assertSame($esperado, Dinheiro::paraCentavos($entrada));
    }

    #[Test]
    public function formata_em_reais(): void
    {
        $this->assertSame('R$ 1.250,50', Dinheiro::formatar(125050));
    }
}
