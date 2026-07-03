<?php

session_start();

// Se já estiver logado, vai direto para a página principal
if (!empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../repository/UsuarioRepository.php';
require_once __DIR__ . '/../entity/Usuario.php';

$erro = '';
$nomeFormulario  = $_POST['nome']  ?? '';
$emailFormulario = $_POST['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome            = $_POST['nome']  ?? '';
    $email           = $_POST['email'] ?? '';
    $senha           = $_POST['senha'] ?? '';
    $confirmarSenha  = $_POST['confirmar_senha'] ?? '';

    try {
        $repo    = new UsuarioRepository();
        $usuario = Usuario::novo($nome, $email, $senha, $confirmarSenha);

        $repo->salvar($usuario);

        // Cadastro feito com sucesso: já loga o usuário automaticamente
        $_SESSION['usuario_id']   = $usuario->getId();
        $_SESSION['usuario_nome'] = $usuario->getNome();

        header('Location: index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $erro = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cadastro — CineRank</title>
  <link rel="stylesheet" href="../assets/style.css" />
</head>
<body class="login-body">

<div class="login-card">
  <div class="login-logo">CineRank</div>
  <h1 class="login-title">Criar conta</h1>
  <p style="text-align: center; margin-bottom: 1.5rem; color: #666;">
    Cadastre-se para organizar, avaliar e ranquear seus filmes e séries favoritos.
  </p>

  <?php if ($erro !== ''): ?>
    <div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <form method="POST" action="cadastro.php">
    <div class="form-group">
      <label for="nome">Nome</label>
      <input
        type="text"
        id="nome"
        name="nome"
        placeholder="Seu nome completo"
        value="<?= htmlspecialchars($nomeFormulario) ?>"
        required
      />
    </div>

    <div class="form-group">
      <label for="email">E-mail</label>
      <input
        type="email"
        id="email"
        name="email"
        placeholder="seu@email.com"
        value="<?= htmlspecialchars($emailFormulario) ?>"
        required
      />
    </div>

    <div class="form-group">
      <label for="senha">Senha</label>
      <input
        type="password"
        id="senha"
        name="senha"
        placeholder="Mínimo de 6 caracteres"
        required
      />
    </div>

    <div class="form-group">
      <label for="confirmar_senha">Confirmar senha</label>
      <input
        type="password"
        id="confirmar_senha"
        name="confirmar_senha"
        placeholder="••••••••"
        required
      />
    </div>

    <button type="submit" class="btn btn-primary btn-full">Criar conta</button>
  </form>

  <p style="text-align: center; margin-top: 1.5rem; color: #888; font-size: 0.9rem;">
    Já tem uma conta? <a href="login.php">Entrar</a>
  </p>

</div>

</body>
</html>
