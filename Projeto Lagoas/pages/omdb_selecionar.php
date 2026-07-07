<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/omdb.php';

header('Content-Type: application/json; charset=utf-8');

$imdbId = trim($_GET['imdb_id'] ?? '');

if ($imdbId === '') {
    echo json_encode(['erro' => 'ID inválido.']);
    exit;
}

$detalhes = omdbDetalhes($imdbId);

if ($detalhes === null) {
    echo json_encode(['erro' => 'Filme não encontrado.']);
    exit;
}

$nomeArquivoImagem = omdbBaixarPoster($detalhes['Poster'] ?? '');

echo json_encode([
    'nome'   => $detalhes['Title'],
    'imagem' => $nomeArquivoImagem, // nome do arquivo já salvo em uploads/
]);
