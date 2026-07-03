<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repository/FilmeRepository.php';
require_once __DIR__ . '/../repository/GeneroRepository.php';
require_once __DIR__ . '/../repository/TagRepository.php';
require_once __DIR__ . '/../includes/upload_imagem.php';

$repo       = new FilmeRepository();
$generoRepo = new GeneroRepository();
$tagRepo    = new TagRepository();

$id = (int) ($_GET['id'] ?? 0);
$filme = $repo->buscarPorId($id, $_SESSION['usuario_id']);

if (!$filme) {
    header('Location: index.php');
    exit;
}

$erro = '';
$nome = $filme->getNome();
$generoId = $filme->getGeneroId();
$nivel = $filme->getNivel();

$generos   = $generoRepo->listarTodos();
$todasTags = $tagRepo->listarTodas();

// Tags já associadas a este filme (para pré-marcar os checkboxes)
$tagsDoFilme = $repo->buscarTagsDoFilme($filme->getId());
$tagIdsSelecionadas = array_map(fn($t) => $t->getId(), $tagsDoFilme);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $generoId = (int) ($_POST['genero_id'] ?? 0);
    $nivel    = (float) ($_POST['nivel'] ?? 1);
    $tagIdsSelecionadas = array_map('intval', $_POST['tags'] ?? []);
    $removerImagemMarcado = isset($_POST['remover_imagem']);

    try {
        $filme->alterarDados($nome, $generoId, $nivel);

        $nomeArquivoImagem = processarUploadImagem($_FILES['imagem'] ?? []);

        if ($nomeArquivoImagem !== null) {
            // Enviou uma imagem nova: apaga a antiga e usa a nova
            removerImagem($filme->getImagem());
            $filme->alterarImagem($nomeArquivoImagem);
        } elseif ($removerImagemMarcado) {
            // Marcou "remover imagem" e não enviou uma nova
            removerImagem($filme->getImagem());
            $filme->removerImagem();
        }
        // Se nenhuma imagem nova e não marcou remover, mantém a imagem atual

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
  <h2>Editar Filme ou Série</h2>
  <a href="index.php" class="btn btn-ghost">← Voltar</a>
</div>

<?php if ($erro !== ''): ?>
  <div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div class="form-card">
  <form method="POST" action="filme_edit.php?id=<?= $filme->getId() ?>" enctype="multipart/form-data">

    <div class="form-group">
      <label for="nome">Título</label>
      <input
        type="text"
        id="nome"
        name="nome"
        placeholder="Ex: Charmander"
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

      <?php if ($filme->getImagem()): ?>
        <div style="margin-bottom: 12px; display:flex; align-items:center; gap:16px;">
          <img
            src="../uploads/<?= htmlspecialchars($filme->getImagem()) ?>"
            alt="Capa atual"
            style="width:100px; border-radius: var(--radius); box-shadow: var(--shadow-sm);"
          />
          <label style="display:flex; align-items:center; gap:6px; font-weight:500; text-transform:none; letter-spacing:0;">
            <input type="checkbox" name="remover_imagem" value="1" />
            Remover imagem atual
          </label>
        </div>
      <?php endif; ?>

      <input
        type="file"
        id="imagem"
        name="imagem"
        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
      />
      <p style="margin-top:6px; color: var(--text-muted); font-size:.8rem;">
        Enviar uma nova imagem substitui a atual automaticamente.
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
      <button type="submit" class="btn btn-primary">Salvar alterações</button>
      <a href="index.php" class="btn btn-ghost">Cancelar</a>
    </div>

  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
