<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repository/FilmeRepository.php';
require_once __DIR__ . '/../includes/upload_imagem.php';

$repo = new FilmeRepository();

$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}

$filme = null;
if ($id > 0) {
    $filme = $repo->buscarPorId($id, $_SESSION['usuario_id']);
}

// Filme não encontrado ou não pertence ao usuário logado
if ($filme === null) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    removerImagem($filme->getImagem());
    $repo->excluir($filme->getId());
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>Excluir Filme ou Série</h2>
  <a href="index.php" class="btn btn-ghost">← Voltar</a>
</div>

<div class="confirm-card">
  <h3>Você tem certeza?</h3>

  <?php if ($filme->getImagem()): ?>
    <img
      src="../uploads/<?= htmlspecialchars($filme->getImagem()) ?>"
      alt="Capa"
      style="width:100px; border-radius: var(--radius); box-shadow: var(--shadow-sm); margin-bottom: 16px;"
    />
  <?php endif; ?>

  <p style="margin-bottom: 2rem; color: #666;">
    Você está prestes a excluir o filme
    <strong><?= htmlspecialchars($filme->getNome()) ?></strong>
    (<?= htmlspecialchars($filme->getGeneroNome()) ?>, Lv. <?= $filme->getNivel() ?>).
    Esta ação não pode ser desfeita.
  </p>

  <form method="POST" action="filme_delete.php?id=<?= $filme->getId() ?>">
    <div class="form-actions">
      <button type="submit" class="btn btn-excluir">Sim, excluir</button>
      <a href="index.php" class="btn btn-ghost">Cancelar</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
