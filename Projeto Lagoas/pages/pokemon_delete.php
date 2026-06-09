<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repository/PokemonRepository.php';

$repo = new PokemonRepository();

$id = 0;
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
}

$pokemon = null;
if ($id > 0) {
    $pokemon = $repo->buscarPorId($id);
}

// Pokémon não encontrado ou não pertence ao usuário logado
if ($pokemon === null || $pokemon->getUsuarioId() !== $_SESSION['usuario_id']) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $repo->excluir($pokemon->getId());
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>Excluir Pokémon</h2>
  <a href="index.php" class="btn btn-ghost">← Voltar</a>
</div>

<div class="confirm-card">
  <h3>Você tem certeza?</h3>
  <p>
    Você está prestes a excluir o pokémon
    <strong><?= htmlspecialchars($pokemon->getNome()) ?></strong>
    (<?= htmlspecialchars($pokemon->getTipo()) ?>, Lv. <?= $pokemon->getNivel() ?>).
    Esta ação não pode ser desfeita.
  </p>

  <form method="POST" action="pokemon_delete.php?id=<?= $pokemon->getId() ?>">
    <div class="form-actions">
      <button type="submit" class="btn btn-excluir">Sim, excluir</button>
      <a href="index.php" class="btn btn-ghost">Cancelar</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
