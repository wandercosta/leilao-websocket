<?php

declare(strict_types=1);

namespace Tests;

use App\Leilao\LanceRecusado;
use App\Leilao\RegraDeLance;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RegraDeLanceTest extends TestCase
{
    private \DateTimeImmutable $agora;
    private \DateTimeImmutable $amanha;

    protected function setUp(): void
    {
        $this->agora = new \DateTimeImmutable('2026-10-21 10:00:00');
        $this->amanha = $this->agora->modify('+1 day');
    }

    #[Test]
    public function primeiro_lance_pode_ser_igual_ao_lance_inicial(): void
    {
        RegraDeLance::validar(50000, 50000, 1000, null, $this->amanha, $this->agora);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function primeiro_lance_abaixo_do_inicial_e_recusado(): void
    {
        $this->expectException(LanceRecusado::class);
        RegraDeLance::validar(49999, 50000, 1000, null, $this->amanha, $this->agora);
    }

    #[Test]
    public function lance_precisa_superar_o_atual_pelo_incremento(): void
    {
        RegraDeLance::validar(51000, 50000, 1000, 50000, $this->amanha, $this->agora);
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function lance_menor_que_o_atual_e_recusado(): void
    {
        $this->expectException(LanceRecusado::class);
        $this->expectExceptionMessage('R$ 510,00');
        RegraDeLance::validar(40000, 50000, 1000, 50000, $this->amanha, $this->agora);
    }

    #[Test]
    public function lance_igual_ao_atual_e_recusado(): void
    {
        $this->expectException(LanceRecusado::class);
        RegraDeLance::validar(50000, 50000, 1000, 50000, $this->amanha, $this->agora);
    }

    #[Test]
    public function leilao_encerrado_nao_aceita_lance(): void
    {
        $this->expectException(LanceRecusado::class);
        $this->expectExceptionMessage('encerrado');
        RegraDeLance::validar(999999, 50000, 1000, null, $this->agora->modify('-1 second'), $this->agora);
    }

    #[Test]
    public function leilao_encerra_exatamente_no_horario(): void
    {
        $this->expectException(LanceRecusado::class);
        RegraDeLance::validar(999999, 50000, 1000, null, $this->agora, $this->agora);
    }
}
