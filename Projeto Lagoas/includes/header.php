<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CineRank</title>
  <link rel="stylesheet" href="../assets/style.css" />
</head>
<body>

<?php
  require_once __DIR__ . '/../repository/FilmeRepository.php';
  require_once __DIR__ . '/../service/NivelService.php';

  $repoFilmeNivel = new FilmeRepository();
  $totalAvaliadosHeader = $repoFilmeNivel->contarPorUsuario($_SESSION['usuario_id']);
  $nivelUsuarioHeader = NivelService::calcular($totalAvaliadosHeader);
?>

<header class="site-header">
  <div class="header-inner">
    <a href="../pages/index.php" class="logo">CineRank</a>

    <nav class="nav">
      <a href="../pages/index.php">Meus Filmes e Séries</a>
      <a href="../pages/filme_create.php">+ Novo Filme ou Série </a>
      <a href="../pages/tag_list.php">Tags</a>
    </nav>

    <div class="header-user">
      <?php
        $nomeUser = $_SESSION['usuario_nome'] ?? 'Usuário';
      ?>
      <span
        class="badge-nivel"
        title="<?= $nivelUsuarioHeader['nivelMaximo']
          ? 'Nível máximo alcançado!'
          : ($nivelUsuarioHeader['faltamParaProximo'] . ' filme(s) para o próximo nível: ' . htmlspecialchars($nivelUsuarioHeader['proximoTitulo'])) ?>"
      >
        <?= $nivelUsuarioHeader['icone'] ?>
        Nível <?= $nivelUsuarioHeader['nivel'] ?> · <?= htmlspecialchars($nivelUsuarioHeader['titulo']) ?>
      </span>
      <span class="user-name">
        <?= htmlspecialchars($nomeUser) ?>
      </span>
      <a href="../pages/logout.php" class="btn-logout">Sair</a>
    </div>
  </div>
</header>

<main class="container">