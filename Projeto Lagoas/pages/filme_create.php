<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repository/FilmeRepository.php';
require_once __DIR__ . '/../repository/GeneroRepository.php';
require_once __DIR__ . '/../repository/TagRepository.php';

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

    try {
        $filme->alterarDados($nome, $generoId, $nivel);
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
  <form method="POST" action="filme_edit.php?id=<?= $filme->getId() ?>">

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
