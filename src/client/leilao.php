<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leilão</title>
    <link rel="stylesheet" href="normalize.css">
    <style>
        /* Adicione seu CSS personalizado aqui */
    </style>
</head>
<body>
<div id="app">
    <h1>Leilão</h1>
    <div id="auction-info">
        <p>Lance inicial: <span id="starting-bid">500</span></p>
        <p>Incremento de lance: <span id="bid-increment">100</span></p>
        <p>Último lance: <span id="last-bid">500</span></p>
    </div>

    <div id="bid-controls">
        <button id="lance-fixo">Ofertar Lance</button>
        <input type="number" id="custom-bid" placeholder="Lance personalizado">
        <button id="place-custom-bid">Ofertar Lance Personalizado</button>
    </div>

    <div id="messages"></div>
    <input type="text" id="message-input">
    <button id="send-message">Enviar</button>
</div>

<script>
    const startingBid = 500;
    const bidIncrement = 100;
    let lastBid = startingBid;

    document.getElementById("lance-fixo").addEventListener("click", () => {
        lastBid += bidIncrement;
        updateLastBid();
    });

    document.getElementById("place-custom-bid").addEventListener("click", () => {
        const customBid = parseInt(document.getElementById("custom-bid").value);
        if (customBid >= lastBid + bidIncrement) {
            lastBid = customBid;
            updateLastBid();
        } else {
            alert("O lance personalizado deve ser maior ou igual ao último lance mais o incremento.");
        }
    });

    function updateLastBid() {
        document.getElementById("last-bid").innerText = lastBid;
        // Enviar o lance atualizado para o websocket aqui
    }

    // Adicione o código WebSocket existente aqui, atualizando as mensagens do leilão conforme necessário
</script>
</body>
</html>
