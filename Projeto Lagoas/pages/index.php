
<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repository/FilmeRepository.php';

$repo = new FilmeRepository();
$pokemons = $repo->listarPorUsuario($_SESSION['usuario_id']);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>Meus Filmes e Séries</h2>
  <a href="pokemon_create.php" class="btn btn-primary">+ Novo Filme</a>
</div>

<?php if (empty($pokemons)): ?>
  <div class="empty-state">
    <p>Você ainda não cadastrou nenhum filme ou série!</p>
    <a href="pokemon_create.php" class="btn btn-primary">Cadastrar agora</a>
  </div>
<?php else: ?>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Título</th>
          <th>Gênero</th>
          <th>Nota</th>
          <th>Data</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pokemons as $pokemon): ?>
          <tr>
            <td><?= $pokemon->getId() ?></td>
            <td><strong><?= htmlspecialchars($pokemon->getNome()) ?></strong></td>
            <td><span class="badge badge-tipo"><?= htmlspecialchars($pokemon->getTipo()) ?></span></td>
            <td>Lv. <?= $pokemon->getNivel() ?></td>
            <td class="acoes">
              <a href="pokemon_edit.php?id=<?= $pokemon->getId() ?>" class="btn btn-sm btn-editar">Editar</a>
              <a href="pokemon_delete.php?id=<?= $pokemon->getId() ?>" class="btn btn-sm btn-excluir">Excluir</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>