<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/omdb.php';

header('Content-Type: application/json; charset=utf-8');

$termo = trim($_GET['termo'] ?? '');

if ($termo === '') {
    echo json_encode([]);
    exit;
}

$resultados = omdbBuscar($termo);

// Envia só o que a tela precisa (evita expor o JSON completo do OMDb)
$lista = array_map(function ($item) {
    return [
        'imdbID' => $item['imdbID'],
        'titulo' => $item['Title'],
        'ano'    => $item['Year'],
        'poster' => $item['Poster'],
    ];
}, $resultados);

echo json_encode($lista);
