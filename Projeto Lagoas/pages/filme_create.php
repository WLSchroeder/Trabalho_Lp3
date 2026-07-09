<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repository/FilmeRepository.php';
require_once __DIR__ . '/../repository/GeneroRepository.php';
require_once __DIR__ . '/../repository/TagRepository.php';
require_once __DIR__ . '/../includes/upload_imagem.php';

$repo       = new FilmeRepository();
$generoRepo = new GeneroRepository();
$tagRepo    = new TagRepository();

$erro = '';
$nome = '';
$generoId = 0;
$nivel = 1;
$tagIdsSelecionadas = [];

$generos   = $generoRepo->listarTodos();
$todasTags = $tagRepo->listarTodas();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $generoId = (int) ($_POST['genero_id'] ?? 0);
    $nivel    = (float) ($_POST['nivel'] ?? 1);
    $tagIdsSelecionadas = array_map('intval', $_POST['tags'] ?? []);

    try {
        $nomeArquivoImagem = processarUploadImagem($_FILES['imagem'] ?? []);

        // Se o usuário não enviou uma imagem manualmente, usa o pôster
        // que já foi baixado do OMDb (guardado no campo escondido imagem_omdb)
        if ($nomeArquivoImagem === null && !empty($_POST['imagem_omdb'])) {
            $nomeArquivoImagem = basename($_POST['imagem_omdb']); // sanitiza o nome
        }

        $filme = Filme::novo($nome, $generoId, $nivel, $_SESSION['usuario_id']);
        if ($nomeArquivoImagem !== null) {
            $filme->alterarImagem($nomeArquivoImagem);
        }

        $repo->salvar($filme);
        $repo->salvarTagsDoFilme($filme->getId(), $tagIdsSelecionadas);

        header('Location: index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $erro = $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>Novo Filme ou Série</h2>
  <a href="index.php" class="btn btn-ghost">← Voltar</a>
</div>

<?php if ($erro !== ''): ?>
  <div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div class="form-card">

  <div class="form-group">
    <label for="omdb_busca">Buscar no OMDb (preenche o título e o pôster automaticamente)</label>
    <div style="display:flex; gap:8px;">
      <input type="text" id="omdb_busca" placeholder="Digite o título e busque..." />
      <button type="button" id="omdb_busca_btn" class="btn btn-ghost">Buscar</button>
    </div>
    <div id="omdb_resultados" style="margin-top:8px; display:flex; flex-direction:column; gap:4px;"></div>
  </div>

  <div id="omdb_preview" style="display:none; align-items:center; gap:12px; margin-bottom:16px;">
    <img id="omdb_preview_img" src="" alt="Pôster selecionado"
         style="width:80px; border-radius: var(--radius); box-shadow: var(--shadow-sm);" />
    <span id="omdb_preview_texto" style="color: var(--text-muted); font-size:.85rem;"></span>
  </div>

  <form method="POST" action="filme_create.php" enctype="multipart/form-data">
    <input type="hidden" id="imagem_omdb" name="imagem_omdb" value="" />

    <div class="form-group">
      <label for="nome">Título</label>
      <input
        type="text"
        id="nome"
        name="nome"
        placeholder="Ex: Avatar"
        value="<?= htmlspecialchars($nome) ?>"
        required
      />
    </div>

    <div class="form-group">
      <label for="genero_id">Gênero</label>
      <select id="genero_id" name="genero_id" required>
        <option value="">Selecione o gênero...</option>
        <?php foreach ($generos as $g): ?>
          <?php $selecionado = ($generoId === $g->getId()) ? 'selected' : ''; ?>
          <option value="<?= $g->getId() ?>" <?= $selecionado ?>>
            <?= htmlspecialchars($g->getNome()) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="nivel">Nível (0 – 10)</label>
      <input
        type="number"
        id="nivel"
        name="nivel"
        min="0"
        max="10"
        step="0.1"
        value="<?= $nivel ?>"
        required
      />
    </div>

    <div class="form-group">
      <label for="imagem">Capa (jpg, png ou webp — até 5 MB)</label>
      <input
        type="file"
        id="imagem"
        name="imagem"
        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
      />
      <p style="margin-top:6px; color: var(--text-muted); font-size:.8rem;">
        Se você buscar um filme no OMDb acima, o pôster já vem preenchido — só envie um
        arquivo aqui se quiser substituir por outra imagem.
      </p>
    </div>

    <div class="form-group">
      <label>Tags</label>
      <?php if (empty($todasTags)): ?>
        <p style="color: var(--text-muted); font-size: .85rem;">
          Nenhuma tag cadastrada ainda.
          <a href="tag_create.php">Criar uma tag</a>
        </p>
      <?php else: ?>
        <div style="display:flex; flex-wrap:wrap; gap:10px;">
          <?php foreach ($todasTags as $tag): ?>
            <label style="display:flex; align-items:center; gap:6px; font-weight:500; text-transform:none; letter-spacing:0;">
              <input
                type="checkbox"
                name="tags[]"
                value="<?= $tag->getId() ?>"
                <?= in_array($tag->getId(), $tagIdsSelecionadas, true) ? 'checked' : '' ?>
              />
              <?= htmlspecialchars($tag->getNome()) ?>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Cadastrar Filme ou Série</button>
      <a href="index.php" class="btn btn-ghost">Cancelar</a>
    </div>

  </form>
</div>

<script>
document.getElementById('omdb_busca_btn').addEventListener('click', function () {
    var termo = document.getElementById('omdb_busca').value.trim();
    var container = document.getElementById('omdb_resultados');
    container.innerHTML = '';

    if (termo === '') {
        return;
    }

    fetch('omdb_buscar.php?termo=' + encodeURIComponent(termo))
        .then(function (resp) { return resp.json(); })
        .then(function (lista) {
            if (lista.length === 0) {
                container.innerHTML = '<p style="color: var(--text-muted); font-size:.85rem;">Nenhum resultado encontrado.</p>';
                return;
            }

            lista.forEach(function (item) {
                var linha = document.createElement('div');
                linha.style.cssText = 'display:flex; align-items:center; gap:10px; cursor:pointer; padding:6px; border-radius:6px;';
                linha.onmouseover = function () { linha.style.background = '#f5f5f5'; };
                linha.onmouseout  = function () { linha.style.background = 'transparent'; };

                var posterSrc = (item.poster && item.poster !== 'N/A') ? item.poster : '';
                linha.innerHTML =
                    (posterSrc ? '<img src="' + posterSrc + '" style="width:40px; border-radius:4px;">' : '') +
                    '<span>' + item.titulo + ' (' + item.ano + ')</span>';

                linha.addEventListener('click', function () {
                    selecionarFilmeOmdb(item.imdbID);
                    document.getElementById('omdb_busca').value = item.titulo;
                    container.innerHTML = '';
                });

                container.appendChild(linha);
            });
        })
        .catch(function () {
            container.innerHTML = '<p style="color: var(--text-muted); font-size:.85rem;">Erro ao buscar. Tente novamente.</p>';
        });
});

function selecionarFilmeOmdb(imdbId) {
    fetch('omdb_selecionar.php?imdb_id=' + encodeURIComponent(imdbId))
        .then(function (resp) { return resp.json(); })
        .then(function (dados) {
            if (dados.erro) {
                alert(dados.erro);
                return;
            }

            document.getElementById('nome').value = dados.nome;
            document.getElementById('imagem_omdb').value = dados.imagem || '';

            var preview = document.getElementById('omdb_preview');
            var previewImg = document.getElementById('omdb_preview_img');
            var previewTexto = document.getElementById('omdb_preview_texto');

            if (dados.imagem) {
                previewImg.src = '../uploads/' + dados.imagem;
                previewTexto.textContent = 'Pôster importado automaticamente do OMDb.';
                preview.style.display = 'flex';
            } else {
                previewTexto.textContent = 'Este título não tem pôster disponível no OMDb.';
                preview.style.display = 'flex';
                previewImg.src = '';
            }
        })
        .catch(function () {
            alert('Erro ao buscar detalhes do filme.');
        });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
