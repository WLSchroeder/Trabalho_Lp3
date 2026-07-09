<?php

require_once __DIR__ . '/../config/omdb.php';

/**
 * Busca filmes/séries no OMDb por palavra-chave (parâmetro "s").
 * Retorna uma lista simples com vários resultados (usada no autocomplete).
 */
function omdbBuscar(string $termo): array {
    $url = 'https://www.omdbapi.com/?apikey=' . OMDB_API_KEY
         . '&s=' . urlencode($termo);

    $json = @file_get_contents($url);
    if ($json === false) {
        return [];
    }

    $dados = json_decode($json, true);

    if (!isset($dados['Response']) || $dados['Response'] === 'False') {
        return [];
    }

    return $dados['Search'] ?? [];
}

/**
 * Busca os detalhes completos de um filme/série pelo ID do IMDb (ex: tt0120338),
 * usando o parâmetro "i" (busca exata, mais confiável que buscar por título).
 */
function omdbDetalhes(string $imdbId): ?array {
    $url = 'https://www.omdbapi.com/?apikey=' . OMDB_API_KEY
         . '&i=' . urlencode($imdbId)
         . '&plot=short';

    $json = @file_get_contents($url);
    if ($json === false) {
        return null;
    }

    $dados = json_decode($json, true);

    if (!isset($dados['Response']) || $dados['Response'] === 'False') {
        return null;
    }

    return $dados;
}

/**
 * Baixa o pôster de uma URL do OMDb e salva na pasta uploads/ do projeto,
 * seguindo o mesmo padrão de nome usado no upload manual (arquivo com nome único).
 * Retorna o nome do arquivo salvo, ou null se não houver pôster disponível.
 */
function omdbBaixarPoster(string $urlPoster): ?string {
    if ($urlPoster === '' || $urlPoster === 'N/A') {
        return null;
    }

    $conteudo = @file_get_contents($urlPoster);
    if ($conteudo === false) {
        return null;
    }

    // O OMDb quase sempre retorna .jpg, mas extraímos da URL por segurança
    $extensao = pathinfo(parse_url($urlPoster, PHP_URL_PATH), PATHINFO_EXTENSION);
    if ($extensao === '') {
        $extensao = 'jpg';
    }

    $nomeArquivo = uniqid('omdb_', true) . '.' . $extensao;

    // Ajuste este caminho se sua pasta uploads/ não estiver um nível acima de includes/
    $caminhoDestino = __DIR__ . '/../uploads/' . $nomeArquivo;

    if (file_put_contents($caminhoDestino, $conteudo) === false) {
        return null;
    }

    return $nomeArquivo;
}
