<!DOCTYPE html>
<html lang="en">
<html>

<head>
    <meta charset="UTF-8">
    <title>WebSocket em PHP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">


    <link rel='stylesheet prefetch' href='https://fonts.googleapis.com/css?family=Open+Sans'>
    <link rel='stylesheet prefetch'
          href='https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.3/jquery.mCustomScrollbar.min.css'>
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <link rel="icon" type="image/png"
          href="https://cdn2.iconfinder.com/data/icons/servers-clouds-colored/80/websocket-web-socket-protocol-512.png">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css" rel="stylesheet">

</head>

<body>
<form method="post" action="../controller/sala.leilao.php?conversationId=1">
    <div class="login">
        <input type="hidden" name="userId" value="2">
        <div class="chat-title">
            <h5> Leilão Wanders</h5>

        </div>
        <div class="messages">
            <div class="form-group">
                <h4>Seja Bem Vindo !!</h4>
            </div>
            <div class="form-group">
                <label style="width: 100% ; margin-top: 20px"> Usuario</label>

                <input name="user" class="btn btn-login center" type="text" value="" placeholder="Digite ...">
            </div>
            <div class="form-group">
                <label style="width: 100%;margin-top: 20px" > Senha</label>
                <input name="password" class="btn  btn-login" type="text" value="" placeholder="Digite ...">
            </div>

        </div>
        <div class="message-box ">
            <button style="width: 94%!important;" id="lance-fixo" class="message-submit w-100">Entrar<span
                        id="proximo-lance"></span></button>

            <input type="hidden" id="ultimo-lance">
        </div>

    </div>
</form>
<div class="bg"></div>
<script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.3/jquery.mCustomScrollbar.concat.min.js'></script>
<!-- <script src="js/index.js"></script> -->
</body>


</html>