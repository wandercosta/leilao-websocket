<!DOCTYPE html>
<html lang="en">
<html>

<?php include "head.php"?>

<body>

<div class="div-leilao">

    <div class="chat-title row">

        <div class="col-lg-6">
            <h1>Leilão - Lote #<?= $id ?></h1>
            <h2 class="nick"> descrição</h2>

        </div>
        <div class="col-lg-6 row" style="justify-content: flex-end;">
            <div class="col-lg-2">
                <button style="font-size: 8px" type="button" class="btn btn-primary">Edital</button>
            </div>
            <div class="col-lg-3">
                <button style="font-size: 8px" type="button" class="btn btn-primary">Regulamento</button>
            </div>
            <div class="col-lg-2">
                <button style="font-size: 8px" type="button" class="btn btn-primary">Detalhes</button>
            </div>
        </div>
    </div>
    <div class="messages">


        <img src="https://www.tribunapr.com.br/wp-content/uploads/sites/1/2019/05/leilao-setran-suv-divulgacao3-w.jpg"
             style="
    width: 100%;
    /* height: 100%; */
">

    </div>
    <div class="message-box row">
        <div class="col-lg-4">
            <p><i class="mdi mdi-account"></i>Lance Inicial: R$ <span id="starting-bid">500,00</span></p>
            <p>Incremento mínimo R$ <span id="bid-increment">100,00</span></p>

        </div>
        <div class="col-lg-5">
            <p>Lance Inicial: R$ <span id="starting-bid">500,00</span></p>
            <p>Incremento mínimo R$ <span id="bid-increment">100,00</span></p>

        </div>
        <div class="col-lg-3" style="border:1px solid; border-radius: 10px">
            <span class="lance-atual lance-atual-titulo"> Maior Lance </span>
            <span class="lance-atual"><i class="mdi mdi-cash-plus"></i><span  class="texto-lance-atual" id="span-ultimo-lance"></span></span>
            <span class="lance-atual"><i class="mdi mdi-account"></i> <span class="texto-lance-atual" id="bid-increment">Juaum Motos</span></span>

        </div>
    </div>

</div>
<div class="chat">

    <div class="chat-title">
        <h1>Servidor WebSocket</h1>
        <h1 id="digitando"></h1>
        <h2 class="nick"><?= $usuarioLogado['nome'] ?></h2>
        <figure class="avatar">
            <img src="https://w7.pngwing.com/pngs/419/473/png-transparent-computer-icons-user-profile-login-user-heroes-sphere-black-thumbnail.png"/>
        </figure>
    </div>
    <div class="messages">

        <div class="messages-content"></div>

    </div>
    <div class="message-box ">
        <button style="width: 94%!important;" id="lance-fixo" class="message-submit w-100">Ofertar Lance R$ <span
                    id="proximo-lance"></span></button>

        <input type="hidden" id="ultimo-lance">
    </div>
    <div class="message-box">
        <textarea id="message-input" class="message-input" placeholder="Digite o Valor do Lance..."
                  style="overflow: hidden;"></textarea>

        <button type="submit" id="place-custom-bid" class="message-submit">Ofertar</button>
    </div>
</div>

<div class="bg"></div>
<script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.3/jquery.mCustomScrollbar.concat.min.js'></script>
<!-- <script src="js/index.js"></script> -->
</body>

<script>

    const inicialLance = 500;
    const incrementoLance = 100;
    let ultimoLance = inicialLance;


    var mySocket;
    // Defina os IDs de conversa para os dois canais
    const conversationId1 = <?= $id ?>;
    // Defina os IDs de conversa para os dois canais
    const userId = <?= $usuarioLogado['id'] ?>;

    const socketMessageListener = (event) => {
        var data = JSON.parse(event.data);

        setTimeout(function () {
            fakeMessage(data.usuario, data.mensagem);
        }, 100);
    };

    const socketOpenListener = (event) => { // Open
        console.log('Connectado');
        const userData = {
            type: 'user_data',
            user: <?= json_encode($usuarioLogado['nome']) ?>,
            conversationId: <?= $id ?>,
            userId: <?= $usuarioLogado['id'] ?>
        };
        mySocket.send(JSON.stringify(userData));
    };

    const socketCloseListener = (event) => { // Closed
        if (mySocket) {
            console.error('Disconnected.');
        }
        mySocket = new WebSocket('ws://localhost:8080');
        mySocket.addEventListener('open', socketOpenListener);
        mySocket.addEventListener('message', socketMessageListener);
        mySocket.addEventListener('close', socketCloseListener);

    };

    socketCloseListener();

    /* chat functions */
    var $messages = $('.messages-content'),
        d, h, m,
        i = 0;

    $(window).load(function () {
        $messages.mCustomScrollbar();
    });

    function updateScrollbar() {
        $messages.mCustomScrollbar("update").mCustomScrollbar('scrollTo', 'bottom', {
            scrollInertia: 10,
            timeout: 0
        });
    }

    function setDate() {
        d = new Date()
        if (m != d.getMinutes()) {
            m = d.getMinutes();
            $('<div class="timestamp">' + d.getHours() + ':' + m + '</div>').appendTo($('.message:last'));
        }
    }


    function insertMessage(msg = null) {
        if (msg <= 0) {
            msg = $('.message-input').val();
        }
        if ($.trim(msg) == '') {
            return false;
        }

        if (msg >= ultimoLance + incrementoLance) {
            ultimoLance = parseInt(msg);
            updateUltimoLance();
        } else {
            alert("O lance  deve ser maior ou igual ao último lance mais o incremento.");
            return false;
        }
        sendMessage(conversationId1, userId, msg);
        $('<div class="message message-personal">' + msg + '</div>').appendTo($('.mCSB_container')).addClass('new');
        setDate();
        $('.message-input').val(null);
        updateScrollbar();
    }

    $('.message-submit').click(function () {
        insertMessage();
    });

    $('#lance-fixo').click(function () {
        insertMessage(ultimoLance + incrementoLance);
    });

    $(window).on('onload', function () {
        insertMessage();
    });


    $(window).on('keydown', function (e) {
        if (e.which == 13) {
            insertMessage();
            return false;
        }
    })

    var Fake = [
        'Bem vindo!<br>Você está conectado!'
    ]

    function fakeMessage(usarioLogado = '', msg = '') {
        if (i == 0 || msg != '') {
            var nomeUser = usarioLogado.nomeUser;
            var dataEnvio = usarioLogado.envio;
            var nomeUser = usarioLogado.nomeUser;
            var propia = usarioLogado.propia;

            updateUltimoLance(msg);

            if (propia == 1) {
                $('<div class="message loading new"><figure class="avatar"><img src="https://w7.pngwing.com/pngs/419/473/png-transparent-computer-icons-user-profile-login-user-heroes-sphere-black-thumbnail.png" /></figure><span></span></div>').appendTo($('.mCSB_container'));
                updateScrollbar();

                $('.message.loading').remove();
                $('<div class="message new" title="' + nomeUser + '"><figure class="avatar"><img src="https://w7.pngwing.com/pngs/419/473/png-transparent-computer-icons-user-profile-login-user-heroes-sphere-black-thumbnail.png" /></figure> <strong>' + (nomeUser != '' ? nomeUser : 'Sistema') + ' </strong><br>' + (msg != '' ? msg : Fake[i]) + ' <div style="font-size: 8px;">' + dataEnvio + '</div></div>').appendTo($('.mCSB_container')).addClass('new');

                setDate();
                updateScrollbar();
                i++;

            } else {
                $('<div class="message message-personal">' + msg + '</div>').appendTo($('.mCSB_container')).addClass('new');
            }
        }
    }

    function sendMessage(conversationId, userId, message) {
        const data = {
            conversationId: conversationId,
            message: message,
            userId: userId
        };

        mySocket.send(JSON.stringify(data));
    }


    function updateUltimoLance(lance = null) {
        if (lance == null) {
            document.getElementById("ultimo-lance").innerText = ultimoLance;
            document.getElementById("span-ultimo-lance").innerText = ultimoLance;
            document.getElementById("proximo-lance").innerText = ultimoLance + incrementoLance;
        } else {
            lance = parseInt(lance);
            document.getElementById("span-ultimo-lance").innerText = lance;
            document.getElementById("ultimo-lance").innerText = lance;
            document.getElementById("proximo-lance").innerText = lance + incrementoLance;
            ultimoLance = parseInt(lance);
        }

        // Enviar o lance atualizado para o websocket aqui
    }



</script>

</html>