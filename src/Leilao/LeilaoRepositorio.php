<?php

declare(strict_types=1);

namespace App\Leilao;

use PDO;

final class LeilaoRepositorio
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function listar(): array
    {
        return array_map([$this, 'hidratar'], $this->pdo->query(
            'SELECT l.*, MAX(b.valor) AS maior_lance, COUNT(b.id) AS total_lances, NOW() AS agora
               FROM leiloes l
               LEFT JOIN lances b ON b.leilao_id = l.id
              GROUP BY l.id
              ORDER BY l.encerra_em <= NOW(), l.encerra_em'
        )->fetchAll());
    }

    /** @return array<string, mixed>|null */
    public function buscar(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT l.*, MAX(b.valor) AS maior_lance, COUNT(b.id) AS total_lances, NOW() AS agora
               FROM leiloes l
               LEFT JOIN lances b ON b.leilao_id = l.id
              WHERE l.id = ?
              GROUP BY l.id'
        );
        $stmt->execute([$id]);
        $leilao = $stmt->fetch();

        return $leilao ? $this->hidratar($leilao) : null;
    }

    /** @return list<array<string, mixed>> Lances mais recentes primeiro. */
    public function ultimosLances(int $leilaoId, int $limite = 30): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT b.id, b.valor, b.criado_em, u.id AS usuario_id, u.nome AS usuario
               FROM lances b
               JOIN usuarios u ON u.id = b.usuario_id
              WHERE b.leilao_id = ?
              ORDER BY b.valor DESC
              LIMIT ' . $limite
        );
        $stmt->execute([$leilaoId]);

        return array_map([$this, 'mapearLance'], $stmt->fetchAll());
    }

    /**
     * Registra o lance dentro de uma transação, travando a linha do leilão,
     * para que dois lances simultâneos não passem com o mesmo valor mínimo.
     *
     * @return array<string, mixed> o lance gravado
     * @throws LanceRecusado
     */
    public function registrarLance(int $leilaoId, int $usuarioId, int $valor): array
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'SELECT lance_inicial, incremento_minimo, encerra_em, NOW() AS agora
                   FROM leiloes WHERE id = ? FOR UPDATE'
            );
            $stmt->execute([$leilaoId]);
            $leilao = $stmt->fetch();

            if (!$leilao) {
                throw new LanceRecusado('Leilão não encontrado.');
            }

            $stmt = $this->pdo->prepare('SELECT MAX(valor) FROM lances WHERE leilao_id = ?');
            $stmt->execute([$leilaoId]);
            $maior = $stmt->fetchColumn();

            RegraDeLance::validar(
                $valor,
                (int) $leilao['lance_inicial'],
                (int) $leilao['incremento_minimo'],
                $maior === null ? null : (int) $maior,
                new \DateTimeImmutable($leilao['encerra_em']),
                new \DateTimeImmutable($leilao['agora']),
            );

            $this->pdo->prepare('INSERT INTO lances (leilao_id, usuario_id, valor) VALUES (?, ?, ?)')
                ->execute([$leilaoId, $usuarioId, $valor]);
            $id = (int) $this->pdo->lastInsertId();

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        $stmt = $this->pdo->prepare(
            'SELECT b.id, b.valor, b.criado_em, u.id AS usuario_id, u.nome AS usuario
               FROM lances b JOIN usuarios u ON u.id = b.usuario_id WHERE b.id = ?'
        );
        $stmt->execute([$id]);

        return $this->mapearLance($stmt->fetch());
    }

    /** @return list<int> ids de leilões que encerraram nos últimos segundos */
    public function encerradosDesde(\DateTimeImmutable $desde): array
    {
        $stmt = $this->pdo->prepare('SELECT id FROM leiloes WHERE encerra_em > ? AND encerra_em <= NOW()');
        $stmt->execute([$desde->format('Y-m-d H:i:s')]);

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function agora(): \DateTimeImmutable
    {
        return new \DateTimeImmutable((string) $this->pdo->query('SELECT NOW()')->fetchColumn());
    }

    /** @param array<string, mixed> $l */
    private function mapearLance(array $l): array
    {
        return [
            'id' => (int) $l['id'],
            'valor' => (int) $l['valor'],
            'usuarioId' => (int) $l['usuario_id'],
            'usuario' => $l['usuario'],
            'criadoEm' => $l['criado_em'],
        ];
    }

    /** @param array<string, mixed> $l */
    private function hidratar(array $l): array
    {
        $maior = $l['maior_lance'] === null ? null : (int) $l['maior_lance'];
        $encerrado = new \DateTimeImmutable($l['agora']) >= new \DateTimeImmutable($l['encerra_em']);

        return [
            'id' => (int) $l['id'],
            'titulo' => $l['titulo'],
            'descricao' => $l['descricao'],
            'imagem' => $l['imagem'],
            'lanceInicial' => (int) $l['lance_inicial'],
            'incremento' => (int) $l['incremento_minimo'],
            'maiorLance' => $maior,
            'totalLances' => (int) $l['total_lances'],
            'minimoAceito' => RegraDeLance::minimoAceito((int) $l['lance_inicial'], (int) $l['incremento_minimo'], $maior),
            'encerraEm' => (new \DateTimeImmutable($l['encerra_em']))->format(DATE_ATOM),
            'segundosRestantes' => max(0, (new \DateTimeImmutable($l['encerra_em']))->getTimestamp() - (new \DateTimeImmutable($l['agora']))->getTimestamp()),
            'encerrado' => $encerrado,
        ];
    }
}
