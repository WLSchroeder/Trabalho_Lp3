
<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repository/UsuarioRepository.php';
require_once __DIR__ . '/../repository/FilmeRepository.php';
require_once __DIR__ . '/../service/NivelService.php';

$usuarioId = (int) ($_GET['id'] ?? 0);

$repoUsuario = new UsuarioRepository();
$usuarioPerfil = $repoUsuario->buscarPorId($usuarioId);

if ($usuarioPerfil === null) {
    header('Location: ranking.php');
    exit;
}

$repoFilme = new FilmeRepository();

// Ordenação por nota, igual à listagem própria
$ordem = $_GET['ordem'] ?? 'desc';
$ordem = (strtolower($ordem) === 'asc') ? 'asc' : 'desc';

$filmes = $repoFilme->listarPorUsuario($usuarioPerfil->getId(), $ordem);
$nivelPerfil = NivelService::calcular(count($filmes));

// Se o usuário clicou no próprio nome no ranking, manda direto pra tela editável
$ehVoceMesmo = $usuarioPerfil->getId() === (int) $_SESSION['usuario_id'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>
    Filmes e Séries de <?= htmlspecialchars($usuarioPerfil->getNome()) ?>
    <?php if ($ehVoceMesmo): ?>
      <span class="badge" style="margin-left:8px;">Você</span>
    <?php endif; ?>
  </h2>
  <a href="ranking.php" class="btn btn-ghost">&larr; Voltar ao ranking</a>
</div>

<div class="nivel-card">
  <div class="nivel-card-icone"><?= $nivelPerfil['icone'] ?></div>
  <div class="nivel-card-info">
    <div class="nivel-card-topo">
      <span class="nivel-card-nivel">Nível <?= $nivelPerfil['nivel'] ?></span>
      <span class="nivel-card-titulo"><?= htmlspecialchars($nivelPerfil['titulo']) ?></span>
    </div>
    <p class="nivel-card-legenda">
      <?= $nivelPerfil['totalAvaliados'] ?> filme(s)/série(s) avaliado(s)
    </p>
  </div>
</div>

<?php if (empty($filmes)): ?>
  <div class="empty-state">
    <p><?= htmlspecialchars($usuarioPerfil->getNome()) ?> ainda não avaliou nenhum filme ou série.</p>
  </div>
<?php else: ?>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Capa</th>
          <th>Título</th>
          <th>Gênero</th>
          <th>Tags</th>
          <th>
            Nota
            <a
              href="perfil.php?id=<?= $usuarioPerfil->getId() ?>&ordem=asc"
              class="ordena-link<?= $ordem === 'asc' ? ' ordena-ativo' : '' ?>"
              title="Ordenar por nota crescente"
            >▲</a>
            <a
              href="perfil.php?id=<?= $usuarioPerfil->getId() ?>&ordem=desc"
              class="ordena-link<?= $ordem === 'desc' ? ' ordena-ativo' : '' ?>"
              title="Ordenar por nota decrescente"
            >▼</a>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($filmes as $filme): ?>
          <?php $tags = $repoFilme->buscarTagsDoFilme($filme->getId()); ?>
          <tr>
            <td><?= $filme->getId() ?></td>
            <td>
              <?php if ($filme->getImagem()): ?>
                <img
                  src="../uploads/<?= htmlspecialchars($filme->getImagem()) ?>"
                  alt="Capa de <?= htmlspecialchars($filme->getNome()) ?>"
                  style="width:48px; height:64px; object-fit:cover; border-radius:6px; box-shadow: var(--shadow-sm);"
                />
              <?php else: ?>
                <span style="color: var(--text-muted); font-size:.8rem;">—</span>
              <?php endif; ?>
            </td>
            <td><strong><?= htmlspecialchars($filme->getNome()) ?></strong></td>
            <td><span class="badge badge-tipo"><?= htmlspecialchars($filme->getGeneroNome()) ?></span></td>
            <td>
              <?php if (empty($tags)): ?>
                <span style="color: var(--text-muted); font-size:.8rem;">—</span>
              <?php else: ?>
                <?php foreach ($tags as $tag): ?>
                  <span class="badge" style="margin: 2px;"><?= htmlspecialchars($tag->getNome()) ?></span>
                <?php endforeach; ?>
              <?php endif; ?>
            </td>
            <td>Lv. <?= $filme->getNivel() ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
