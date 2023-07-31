<?php
if (!isset( $_GET['conversationId']) ) {
    header("Location: http://localhost/leilao/src/client/controller/login.php");
    exit();
}

$id = $_GET['conversationId'];


include "header.php";
include "../view/sala.leilao.php";
