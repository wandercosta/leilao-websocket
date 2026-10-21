<?php

use App\Leilao\Dinheiro;

$titulo = 'Lotes · Leilão ao vivo';
?>
<h1 class="titulo-pagina">Lotes</h1>

<div class="grade-lotes">
    <?php foreach ($leiloes as $l): ?>
        <a class="cartao lote <?= $l['encerrado'] ? 'lote-encerrado' : '' ?>" href="/leilao/<?= $l['id'] ?>">
            <img src="<?= $e($l['imagem']) ?>" alt="" loading="lazy">
            <div class="lote-corpo">
                <span class="selo <?= $l['encerrado'] ? 'selo-encerrado' : 'selo-aberto' ?>">
                    <?= $l['encerrado'] ? 'Encerrado' : 'Aberto' ?>
                </span>
                <h2><?= $e($l['titulo']) ?></h2>
                <p class="suave">
                    <?= $l['maiorLance'] === null ? 'Lance inicial' : 'Maior lance' ?>
                    <strong><?= Dinheiro::formatar($l['maiorLance'] ?? $l['lanceInicial']) ?></strong>
                    · <?= $l['totalLances'] ?> lance(s)
                </p>
            </div>
        </a>
    <?php endforeach ?>
</div>
