<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../repository/FilmeRepository.php';

$repo = new FilmeRepository();

// Ordenação por nota (nível): 'asc' = crescente, 'desc' = decrescente (padrão)
$ordem = $_GET['ordem'] ?? 'desc';
$ordem = (strtolower($ordem) === 'asc') ? 'asc' : 'desc';

$filmes = $repo->listarPorUsuario($_SESSION['usuario_id'], $ordem);

require_once __DIR__ . '/../service/NivelService.php';
$nivelUsuario = NivelService::calcular(count($filmes));

require_once __DIR__ . '/../includes/header.php';
?>

<div class="nivel-card">
  <div class="nivel-card-icone"><?= $nivelUsuario['icone'] ?></div>
  <div class="nivel-card-info">
    <div class="nivel-card-topo">
      <span class="nivel-card-nivel">Nível <?= $nivelUsuario['nivel'] ?></span>
      <span class="nivel-card-titulo"><?= htmlspecialchars($nivelUsuario['titulo']) ?></span>
    </div>

    <?php if ($nivelUsuario['nivelMaximo']): ?>
      <p class="nivel-card-legenda">
        Você atingiu o nível máximo com <?= $nivelUsuario['totalAvaliados'] ?> filme(s)/série(s) avaliado(s). Parabéns, lenda! 🌟
      </p>
    <?php else: ?>
      <div class="nivel-progresso-barra">
        <div class="nivel-progresso-preenchido" style="width: <?= $nivelUsuario['progresso'] ?>%;"></div>
      </div>
      <p class="nivel-card-legenda">
        <?= $nivelUsuario['totalAvaliados'] ?> avaliado(s) —
        faltam <strong><?= $nivelUsuario['faltamParaProximo'] ?></strong>
        para o nível <?= $nivelUsuario['nivel'] + 1 ?> (<?= htmlspecialchars($nivelUsuario['proximoTitulo']) ?>)
      </p>
    <?php endif; ?>
  </div>
</div>

<div class="page-header">
  <h2>Meus Filmes e Séries</h2>
  <a href="filme_create.php" class="btn btn-primary">+ Novo Filme</a>
</div>

<?php if (empty($filmes)): ?>
  <div class="empty-state">
    <p>Você ainda não cadastrou nenhum filme ou série!</p>
    <a href="filme_create.php" class="btn btn-primary">Cadastrar agora</a>
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
              href="index.php?ordem=asc"
              class="ordena-link<?= $ordem === 'asc' ? ' ordena-ativo' : '' ?>"
              title="Ordenar por nota crescente"
            >▲</a>
            <a
              href="index.php?ordem=desc"
              class="ordena-link<?= $ordem === 'desc' ? ' ordena-ativo' : '' ?>"
              title="Ordenar por nota decrescente"
            >▼</a>
          </th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($filmes as $filme): ?>
          <?php $tags = $repo->buscarTagsDoFilme($filme->getId()); ?>
          <tr>
            <td><?= $filme->getId() ?></td>
            <td>
              <?php if ($filme->getImagem()): ?>
                <img
                  src="../uploads/<?= htmlspecialchars($filme->getImagem()) ?>"
                  alt="Capa de <?= htmlspecialchars($filme->getNome()) ?>"
                  style="width:64px; height:86px; object-fit:cover; border-radius:6px; box-shadow: var(--shadow-sm); display:block;"
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
                <div class="tags-linha">
                  <?php foreach ($tags as $tag): ?>
                    <span class="badge" style="white-space:nowrap;"><?= htmlspecialchars($tag->getNome()) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <span
                class="badge-nota"
                style="background: <?= corNota($filme->getNivel()) ?>;"
              ><?= number_format($filme->getNivel(), 1) ?></span>
            </td>
            <td class="acoes">
              <a href="filme_edit.php?id=<?= $filme->getId() ?>" class="btn btn-sm btn-editar">Editar</a>
              <a href="filme_delete.php?id=<?= $filme->getId() ?>" class="btn btn-sm btn-excluir">Excluir</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>