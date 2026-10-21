<?php

use App\Leilao\Dinheiro;

$titulo = $leilao['titulo'] . ' · Leilão ao vivo';
$scripts = ['/js/sala.js'];
?>
<a class="voltar" href="/">← Lotes</a>

<div class="sala"
     id="sala"
     data-ws-url="<?= $e($wsUrl) ?>"
     data-token="<?= $e($token) ?>"
     data-leilao-id="<?= $leilao['id'] ?>"
     data-usuario-id="<?= $usuario['id'] ?>">

    <section class="cartao sala-lote">
        <img src="<?= $e($leilao['imagem']) ?>" alt="">
        <div class="sala-lote-corpo">
            <p class="suave">Lote #<?= $leilao['id'] ?></p>
            <h1><?= $e($leilao['titulo']) ?></h1>
            <p><?= $e($leilao['descricao']) ?></p>

            <dl class="numeros">
                <div><dt>Lance inicial</dt><dd><?= Dinheiro::formatar($leilao['lanceInicial']) ?></dd></div>
                <div><dt>Incremento mínimo</dt><dd><?= Dinheiro::formatar($leilao['incremento']) ?></dd></div>
                <div><dt>Encerra em</dt><dd id="cronometro" data-segundos="<?= $leilao['segundosRestantes'] ?>">–</dd></div>
            </dl>
        </div>
    </section>

    <section class="cartao sala-lances">
        <div class="status-conexao" id="status-conexao" data-estado="conectando">Conectando…</div>

        <div class="maior-lance" id="maior-lance">
            <span class="suave">Maior lance</span>
            <strong id="maior-valor"><?= Dinheiro::formatar($leilao['maiorLance'] ?? $leilao['lanceInicial']) ?></strong>
            <span id="maior-usuario" class="suave"><?= $leilao['maiorLance'] === null ? 'nenhum lance ainda' : '' ?></span>
        </div>

        <form class="formulario-lance" id="formulario-lance" autocomplete="off">
            <button class="botao botao-grande" type="button" id="lance-rapido" disabled>
                Dar lance de <span id="proximo-minimo"><?= Dinheiro::formatar($leilao['minimoAceito']) ?></span>
            </button>
            <div class="lance-livre">
                <label class="sr-only" for="valor-livre">Outro valor</label>
                <input id="valor-livre" name="valor" inputmode="decimal" placeholder="Outro valor, ex.: 1.500,00" disabled>
                <button class="botao botao-secundario" type="submit" disabled>Ofertar</button>
            </div>
        </form>

        <p class="alerta" id="aviso" role="status" hidden></p>

        <div class="presenca" id="presenca"></div>

        <h2 class="historico-titulo">Lances</h2>
        <ol class="historico" id="historico"></ol>
    </section>
</div>
