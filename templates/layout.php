<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($titulo ?? 'Leilão ao vivo') ?></title>
    <link rel="icon" href="/img/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
<header class="topo">
    <a class="marca" href="/"><span class="marca-ponto"></span> Leilão<strong>ao vivo</strong></a>
    <?php if (!empty($usuario)): ?>
        <form class="topo-usuario" method="post" action="/logout">
            <span>Olá, <strong><?= $e($usuario['nome']) ?></strong></span>
            <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
            <button class="botao-link" type="submit">Sair</button>
        </form>
    <?php endif ?>
</header>

<main class="conteudo">
    <?= $conteudo ?>
</main>

<?php foreach ($scripts ?? [] as $script): ?>
    <script src="<?= $e($script) ?>"></script>
<?php endforeach ?>
</body>
</html>
