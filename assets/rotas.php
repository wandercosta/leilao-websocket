<?php


$urlreservada[""] = "index.php";
$urlreservada["sala-leilao"] = "sala.leilao.php";


$current_url = $_SERVER['REQUEST_URI'];

// Analisar a URL atual
$parsed_url = parse_url($current_url);

// Dividir o caminho da URL em segmentos
$url = explode('/', $parsed_url['path']);

// Remover o primeiro elemento se estiver vazio (caso o caminho comece com uma barra '/')
if ($url[0] === '') {
    array_shift($url);
}


if($urlreservada[$url[2]]){
    include($config['urlPrincipalCotnroller'].$urlreservada[$url[2]]);
} else {
    $urlreservada[$url[2]] = "index.php";
    include($config['urlPrincipalCotnroller'] . "index.php");
}