<?php

declare(strict_types=1);

namespace Tests;

use App\Auth\TokenDeSala;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TokenDeSalaTest extends TestCase
{
    private const SEGREDO = 'segredo-de-teste-com-mais-de-32-caracteres';

    #[Test]
    public function token_valido_devolve_o_usuario(): void
    {
        $tokens = new TokenDeSala(self::SEGREDO);
        $this->assertSame(['uid' => 7, 'nome' => 'Ana'], $tokens->validar($tokens->emitir(7, 'Ana')));
    }

    #[Test]
    public function token_adulterado_e_rejeitado(): void
    {
        $tokens = new TokenDeSala(self::SEGREDO);
        [, $assinatura] = explode('.', $tokens->emitir(7, 'Ana'));
        $payloadFalso = rtrim(strtr(base64_encode('{"uid":1,"nome":"Admin","exp":9999999999}'), '+/', '-_'), '=');

        $this->assertNull($tokens->validar("{$payloadFalso}.{$assinatura}"));
    }

    #[Test]
    public function token_de_outro_segredo_e_rejeitado(): void
    {
        $outro = new TokenDeSala(str_repeat('x', 40));
        $this->assertNull((new TokenDeSala(self::SEGREDO))->validar($outro->emitir(7, 'Ana')));
    }

    #[Test]
    public function token_expirado_e_rejeitado(): void
    {
        $tokens = new TokenDeSala(self::SEGREDO);
        $this->assertNull($tokens->validar($tokens->emitir(7, 'Ana', -1)));
    }
}
