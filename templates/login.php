<?php $titulo = 'Entrar · Leilão ao vivo' ?>
<section class="cartao login">
    <h1>Entrar</h1>
    <p class="suave">Use um dos usuários de demonstração abaixo.</p>

    <?php if ($erro): ?>
        <p class="alerta alerta-erro" role="alert"><?= $e($erro) ?></p>
    <?php endif ?>

    <form method="post" action="/login" class="formulario">
        <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
        <label>
            E-mail
            <input type="email" name="email" value="<?= $e($email) ?>" required autofocus autocomplete="username">
        </label>
        <label>
            Senha
            <input type="password" name="senha" required autocomplete="current-password">
        </label>
        <button class="botao" type="submit">Entrar</button>
    </form>

    <details class="demo" open>
        <summary>Usuários de demonstração</summary>
        <ul>
            <li><code>ana@exemplo.com</code></li>
            <li><code>bruno@exemplo.com</code></li>
            <li><code>carla@exemplo.com</code></li>
        </ul>
        <p class="suave">Senha de todos: <code>demo1234</code> (dados fictícios)</p>
    </details>
</section>
