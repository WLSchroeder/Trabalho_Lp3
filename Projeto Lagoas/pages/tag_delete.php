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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $repo->excluir($tag->getId());
    header('Location: tag_list.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>Excluir Tag</h2>
  <a href="tag_list.php" class="btn btn-ghost">← Voltar</a>
</div>

<div class="confirm-card">
  <h3>Você tem certeza?</h3>
  <p style="margin-bottom: 2rem; color: #666;">
    Você está prestes a excluir a tag
    <strong><?= htmlspecialchars($tag->getNome()) ?></strong>.
    Ela será removida de todos os filmes que a usam. Esta ação não pode ser desfeita.
  </p>

  <form method="POST" action="tag_delete.php?id=<?= $tag->getId() ?>">
    <div class="form-actions">
      <button type="submit" class="btn btn-excluir">Sim, excluir</button>
      <a href="tag_list.php" class="btn btn-ghost">Cancelar</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
