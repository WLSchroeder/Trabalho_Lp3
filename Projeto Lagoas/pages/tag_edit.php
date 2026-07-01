<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repository/TagRepository.php';

$repo = new TagRepository();

$id = (int) ($_GET['id'] ?? 0);
$tag = $repo->buscarPorId($id);

if (!$tag) {
    header('Location: tag_list.php');
    exit;
}

$erro = '';
$nome = $tag->getNome();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');

    try {
        $tag->alterarNome($nome);
        $repo->salvar($tag);

        header('Location: tag_list.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $erro = $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>Editar Tag</h2>
  <a href="tag_list.php" class="btn btn-ghost">← Voltar</a>
</div>

<?php if ($erro !== ''): ?>
  <div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div class="form-card">
  <form method="POST" action="tag_edit.php?id=<?= $tag->getId() ?>">

    <div class="form-group">
      <label for="nome">Nome da tag</label>
      <input
        type="text"
        id="nome"
        name="nome"
        value="<?= htmlspecialchars($nome) ?>"
        maxlength="50"
        required
      />
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Salvar alterações</button>
      <a href="tag_list.php" class="btn btn-ghost">Cancelar</a>
    </div>

  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
