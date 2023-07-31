<?php
include $_SERVER['DOCUMENT_ROOT']."/leilao/assets/config.php";

session_start();
$_SESSION['user'] = $_SESSION['user']?:$_POST['user'];

$usuarioLogado['nome'] =  $_SESSION['user'];
$usuarioLogado['id'] =  1;
if (!isset($usuarioLogado['nome']) ) {

    header("Location: http://localhost/leilao/src/client/controller/login.php");
    exit();
}




?>