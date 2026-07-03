<?php

/**
 * Helper de upload de imagem.
 * - Aceita apenas jpg, png, webp
 * - Limite bruto de 5 MB (antes de processar)
 * - Redimensiona toda imagem para um padrão fixo (largura máxima 400px,
 *   mantendo a proporção) e salva sempre como .jpg com qualidade 85
 * - Isso garante tamanho e qualidade uniformes independente do que o usuário envie
 */

const UPLOAD_DIR_FS  = __DIR__ . '/../uploads/';   // caminho físico no servidor
const UPLOAD_DIR_WEB = 'uploads/';                 // caminho relativo salvo no banco / usado no <img src>

const UPLOAD_MAX_BYTES = 5 * 1024 * 1024; // 5 MB brutos, antes da compressão
const UPLOAD_LARGURA_PADRAO = 400;        // largura final padronizada, em pixels
const UPLOAD_QUALIDADE_JPG  = 85;         // qualidade de compressão do JPG final

const UPLOAD_MIME_PERMITIDOS = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

/**
 * Processa o upload vindo de $_FILES['imagem'], normaliza e salva em uploads/.
 * Retorna o nome do arquivo salvo (para gravar no banco) ou null se nenhum
 * arquivo foi enviado (campo opcional).
 * Lança InvalidArgumentException em caso de erro de validação.
 */
function processarUploadImagem(array $arquivo): ?string {
    // Nenhum arquivo selecionado: não é erro, apenas mantém a imagem atual
    if (!isset($arquivo['error']) || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('Falha ao enviar a imagem. Tente novamente.');
    }

    if ($arquivo['size'] > UPLOAD_MAX_BYTES) {
        throw new InvalidArgumentException('A imagem deve ter no máximo 5 MB.');
    }

    // Valida o tipo real do arquivo (não confia na extensão nem no mime enviado pelo navegador)
    $infoImagem = @getimagesize($arquivo['tmp_name']);
    if ($infoImagem === false) {
        throw new InvalidArgumentException('O arquivo enviado não é uma imagem válida.');
    }

    $mimeReal = $infoImagem['mime'];
    if (!isset(UPLOAD_MIME_PERMITIDOS[$mimeReal])) {
        throw new InvalidArgumentException('Formato inválido. Envie apenas JPG, PNG ou WEBP.');
    }

    // Cria a imagem de origem de acordo com o tipo real detectado
    switch ($mimeReal) {
        case 'image/jpeg':
            $origem = imagecreatefromjpeg($arquivo['tmp_name']);
            break;
        case 'image/png':
            $origem = imagecreatefrompng($arquivo['tmp_name']);
            break;
        case 'image/webp':
            $origem = imagecreatefromwebp($arquivo['tmp_name']);
            break;
        default:
            throw new InvalidArgumentException('Formato inválido.');
    }

    if ($origem === false) {
        throw new InvalidArgumentException('Não foi possível processar a imagem enviada.');
    }

    // Calcula as novas dimensões mantendo a proporção
    $larguraOriginal = imagesx($origem);
    $alturaOriginal   = imagesy($origem);

    $novaLargura = UPLOAD_LARGURA_PADRAO;
    $novaAltura  = (int) round($alturaOriginal * ($novaLargura / $larguraOriginal));

    // Não amplia imagens menores que o padrão, apenas reduz as maiores
    if ($larguraOriginal < UPLOAD_LARGURA_PADRAO) {
        $novaLargura = $larguraOriginal;
        $novaAltura  = $alturaOriginal;
    }

    $destino = imagecreatetruecolor($novaLargura, $novaAltura);

    // Fundo branco (evita fundo preto ao converter PNG/WEBP com transparência para JPG)
    $branco = imagecolorallocate($destino, 255, 255, 255);
    imagefill($destino, 0, 0, $branco);

    imagecopyresampled(
        $destino, $origem,
        0, 0, 0, 0,
        $novaLargura, $novaAltura,
        $larguraOriginal, $alturaOriginal
    );

    if (!is_dir(UPLOAD_DIR_FS)) {
        mkdir(UPLOAD_DIR_FS, 0755, true);
    }

    $nomeArquivo = uniqid('filme_', true) . '.jpg';
    $caminhoFs   = UPLOAD_DIR_FS . $nomeArquivo;

    imagejpeg($destino, $caminhoFs, UPLOAD_QUALIDADE_JPG);

    imagedestroy($origem);
    imagedestroy($destino);

    return $nomeArquivo;
}

/**
 * Remove o arquivo de imagem do disco (uploads/), se existir.
 */
function removerImagem(?string $nomeArquivo): void {
    if (!$nomeArquivo) {
        return;
    }

    $caminho = UPLOAD_DIR_FS . $nomeArquivo;
    if (is_file($caminho)) {
        unlink($caminho);
    }
}
