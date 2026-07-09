
<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repository/UsuarioRepository.php';
require_once __DIR__ . '/../service/NivelService.php';

$repoUsuario = new UsuarioRepository();
$ranking = $repoUsuario->listarRanking();

// Calcula o nível de cada usuário e ordena pelo nível (e, em caso de
// empate no nível, por quem avaliou mais filmes).
foreach ($ranking as &$linha) {
    $linha['nivelInfo'] = NivelService::calcular($linha['totalAvaliados']);
}
unset($linha);

usort($ranking, function ($a, $b) {
    if ($a['nivelInfo']['nivel'] !== $b['nivelInfo']['nivel']) {
        return $b['nivelInfo']['nivel'] <=> $a['nivelInfo']['nivel'];
    }
    return $b['totalAvaliados'] <=> $a['totalAvaliados'];
});

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>Ranking de Cinéfilos</h2>
</div>

<?php if (empty($ranking)): ?>
  <div class="empty-state">
    <p>Ainda não há usuários cadastrados para exibir no ranking.</p>
  </div>
<?php else: ?>
  <div class="table-wrapper">
    <table class="data-table">
      <thead>
        <tr>
          <th>Posição</th>
          <th>Usuário</th>
          <th>Nível</th>
          <th>Título</th>
          <th>Filmes/Séries avaliados</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ranking as $posicao => $linha): ?>
          <?php
            $ehVoce = $linha['id'] === (int) $_SESSION['usuario_id'];
            $medalha = match ($posicao) {
                0       => '🥇',
                1       => '🥈',
                2       => '🥉',
                default => null,
            };
          ?>
          <tr class="<?= $ehVoce ? 'linha-voce' : '' ?>">
            <td><?= $medalha ?? ($posicao + 1) ?></td>
            <td>
              <a href="perfil.php?id=<?= $linha['id'] ?>">
                <strong><?= htmlspecialchars($linha['nome']) ?></strong>
              </a>
              <?php if ($ehVoce): ?>
                <span class="badge" style="margin-left:6px;">Você</span>
              <?php endif; ?>
            </td>
            <td>Nível <?= $linha['nivelInfo']['nivel'] ?></td>
            <td>
              <?= $linha['nivelInfo']['icone'] ?>
              <?= htmlspecialchars($linha['nivelInfo']['titulo']) ?>
            </td>
            <td><?= $linha['totalAvaliados'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
