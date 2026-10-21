<?php

declare(strict_types=1);

namespace App\Auth;

/**
 * Token assinado (HMAC-SHA256) que o site entrega ao navegador depois do login.
 * O servidor WebSocket não enxerga a sessão PHP, então é assim que ele sabe
 * quem é o usuário de cada conexão sem confiar no que o navegador diz.
 */
final class TokenDeSala
{
    public function __construct(private readonly string $segredo)
    {
        if (strlen($segredo) < 32) {
            throw new \InvalidArgumentException('APP_SECRET precisa ter pelo menos 32 caracteres.');
        }
    }

    public function emitir(int $usuarioId, string $nome, int $validadeEmSegundos = 7200): string
    {
        $payload = self::base64url(json_encode([
            'uid' => $usuarioId,
            'nome' => $nome,
            'exp' => time() + $validadeEmSegundos,
        ], JSON_THROW_ON_ERROR));

        return $payload . '.' . $this->assinar($payload);
    }

    /** @return array{uid:int, nome:string}|null */
    public function validar(string $token): ?array
    {
        $partes = explode('.', $token);
        if (count($partes) !== 2) {
            return null;
        }

        [$payload, $assinatura] = $partes;
        if (!hash_equals($this->assinar($payload), $assinatura)) {
            return null;
        }

        $dados = json_decode((string) base64_decode(strtr($payload, '-_', '+/')), true);
        if (!is_array($dados) || ($dados['exp'] ?? 0) < time()) {
            return null;
        }

        return ['uid' => (int) $dados['uid'], 'nome' => (string) $dados['nome']];
    }

    private function assinar(string $payload): string
    {
        return self::base64url(hash_hmac('sha256', $payload, $this->segredo, true));
    }

    private static function base64url(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
