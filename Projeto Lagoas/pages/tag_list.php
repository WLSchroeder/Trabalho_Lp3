<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repository/TagRepository.php';

$repo = new TagRepository();
$tags = $repo->listarTodas();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>Tags</h2>
  <a href="tag_create.php" class="btn btn-primary">+ Nova Tag</a>
</div>

<?php if (empty($tags)): ?>
  <div class="empty-state">
    <p>Nenhuma tag cadastrada ainda.</p>
    <a href="tag_create.php" class="btn btn-primary">Cadastrar agora</a>
  </div>
<?php else: ?>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Nome</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tags as $tag): ?>
          <tr>
            <td><?= $tag->getId() ?></td>
            <td><strong><?= htmlspecialchars($tag->getNome()) ?></strong></td>
            <td class="acoes">
              <a href="tag_edit.php?id=<?= $tag->getId() ?>" class="btn btn-sm btn-editar">Editar</a>
              <a href="tag_delete.php?id=<?= $tag->getId() ?>" class="btn btn-sm btn-excluir">Excluir</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
