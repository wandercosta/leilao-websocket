<?php

declare(strict_types=1);

namespace App\WebSocket;

use App\Auth\TokenDeSala;
use App\Leilao\Dinheiro;
use App\Leilao\LanceRecusado;
use App\Leilao\LeilaoRepositorio;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * Servidor WebSocket do leilão.
 *
 * Protocolo (JSON):
 *   cliente → {"tipo":"entrar", "token":"...", "leilaoId":1}
 *   cliente → {"tipo":"lance", "valor":"1.250,00"}
 *   servidor → {"tipo":"estado", ...}        snapshot ao entrar
 *   servidor → {"tipo":"novo_lance", ...}    para todos da sala
 *   servidor → {"tipo":"encerrado", ...}     para todos da sala
 *   servidor → {"tipo":"presenca", ...}      quantas pessoas estão na sala
 *   servidor → {"tipo":"erro", "mensagem"}   só para quem errou
 */
final class SalaDeLeilao implements MessageComponentInterface
{
    /** @var \SplObjectStorage<ConnectionInterface, array{uid:int, nome:string, leilaoId:int}|null> */
    private \SplObjectStorage $conexoes;

    private \DateTimeImmutable $ultimaVerificacao;

    /** @param \Closure(): LeilaoRepositorio $repositorio fábrica, para reconectar ao banco se a conexão cair */
    public function __construct(
        private readonly \Closure $repositorio,
        private readonly TokenDeSala $tokens,
    ) {
        $this->conexoes = new \SplObjectStorage();
        $this->ultimaVerificacao = ($this->repositorio)()->agora();
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->conexoes[$conn] = null;
        $this->log("conexão #{$conn->resourceId} aberta");
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $dados = json_decode((string) $msg, true);

        if (!is_array($dados) || !isset($dados['tipo'])) {
            $this->erro($from, 'Mensagem inválida.');
            return;
        }

        match ($dados['tipo']) {
            'entrar' => $this->entrar($from, $dados),
            'lance' => $this->lance($from, $dados),
            default => $this->erro($from, 'Tipo de mensagem desconhecido.'),
        };
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $sessao = $this->conexoes[$conn] ?? null;
        $this->conexoes->detach($conn);
        $this->log("conexão #{$conn->resourceId} fechada");

        if ($sessao) {
            $this->enviarPresenca($sessao['leilaoId']);
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $this->log("erro na conexão #{$conn->resourceId}: {$e->getMessage()}");
        $conn->close();
    }

    /** Chamado a cada segundo pelo loop: avisa as salas cujo leilão acabou de encerrar. */
    public function verificarEncerramentos(): void
    {
        $repo = ($this->repositorio)();
        $agora = $repo->agora();

        foreach ($repo->encerradosDesde($this->ultimaVerificacao) as $leilaoId) {
            $leilao = $repo->buscar($leilaoId);
            $this->transmitir($leilaoId, ['tipo' => 'encerrado', 'leilao' => $leilao]);
            $this->log("leilão #{$leilaoId} encerrado");
        }

        $this->ultimaVerificacao = $agora;
    }

    /** @param array<string, mixed> $dados */
    private function entrar(ConnectionInterface $conn, array $dados): void
    {
        $usuario = $this->tokens->validar((string) ($dados['token'] ?? ''));
        if (!$usuario) {
            $this->erro($conn, 'Sessão expirada. Faça login novamente.');
            $conn->close();
            return;
        }

        $repo = ($this->repositorio)();
        $leilaoId = (int) ($dados['leilaoId'] ?? 0);
        $leilao = $repo->buscar($leilaoId);
        if (!$leilao) {
            $this->erro($conn, 'Leilão não encontrado.');
            return;
        }

        $this->conexoes[$conn] = $usuario + ['leilaoId' => $leilaoId];
        $this->log("{$usuario['nome']} entrou no leilão #{$leilaoId}");

        $this->enviar($conn, [
            'tipo' => 'estado',
            'leilao' => $leilao,
            'lances' => $repo->ultimosLances($leilaoId),
        ]);
        $this->enviarPresenca($leilaoId);
    }

    /** @param array<string, mixed> $dados */
    private function lance(ConnectionInterface $conn, array $dados): void
    {
        $sessao = $this->conexoes[$conn] ?? null;
        if (!$sessao) {
            $this->erro($conn, 'Entre na sala antes de dar lances.');
            return;
        }

        $valor = Dinheiro::paraCentavos($dados['valor'] ?? '');
        if ($valor === null) {
            $this->erro($conn, 'Informe um valor de lance válido.');
            return;
        }

        $repo = ($this->repositorio)();

        try {
            $lance = $repo->registrarLance($sessao['leilaoId'], $sessao['uid'], $valor);
        } catch (LanceRecusado $e) {
            $this->erro($conn, $e->getMessage());
            $this->log("lance recusado de {$sessao['nome']}: {$e->getMessage()}");
            return;
        }

        $this->log("{$sessao['nome']} deu " . Dinheiro::formatar($valor) . " no leilão #{$sessao['leilaoId']}");

        $this->transmitir($sessao['leilaoId'], [
            'tipo' => 'novo_lance',
            'lance' => $lance,
            'leilao' => $repo->buscar($sessao['leilaoId']),
        ]);
    }

    private function enviarPresenca(int $leilaoId): void
    {
        $nomes = [];
        foreach ($this->conexoes as $conn) {
            $s = $this->conexoes[$conn];
            if ($s && $s['leilaoId'] === $leilaoId) {
                $nomes[$s['uid']] = $s['nome'];
            }
        }

        $this->transmitir($leilaoId, ['tipo' => 'presenca', 'participantes' => array_values($nomes)]);
    }

    /** @param array<string, mixed> $mensagem */
    private function transmitir(int $leilaoId, array $mensagem): void
    {
        foreach ($this->conexoes as $conn) {
            $s = $this->conexoes[$conn];
            if ($s && $s['leilaoId'] === $leilaoId) {
                $this->enviar($conn, $mensagem);
            }
        }
    }

    /** @param array<string, mixed> $mensagem */
    private function enviar(ConnectionInterface $conn, array $mensagem): void
    {
        $conn->send(json_encode($mensagem, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    private function erro(ConnectionInterface $conn, string $mensagem): void
    {
        $this->enviar($conn, ['tipo' => 'erro', 'mensagem' => $mensagem]);
    }

    private function log(string $texto): void
    {
        echo '[' . date('H:i:s') . "] {$texto}\n";
    }
}
