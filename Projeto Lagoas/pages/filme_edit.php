<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../repository/PokemonRepository.php';

$repo = new PokemonRepository();

$id = (int) ($_GET['id'] ?? 0);
$pokemon = $repo->buscarPorId($id, $_SESSION['usuario_id']);

if (!$pokemon) {
    header('Location: index.php');
    exit;
}

$erro = '';
$nome = $pokemon->getNome();
$tipo = $pokemon->getTipo();
$nivel = $pokemon->getNivel();

$tipos = [
    'Ação',
    'Aventura',
    'Animação',
    'Comédia',
    'Crime',
    'Documentário',
    'Drama',
    'Fantasia',
    'Ficção Científica',
    'Mistério',
    'Romance',
    'Suspense',
    'Terror'
];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $tipo  = trim($_POST['tipo'] ?? '');
    $nivel = (int) ($_POST['nivel'] ?? 1);

    try {
        $pokemon->alterarDados($nome, $tipo, $nivel);
        $repo->salvar($pokemon);

        header('Location: index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $erro = $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h2>Editar Filme ou Série</h2>
  <a href="index.php" class="btn btn-ghost">← Voltar</a>
</div>

<?php if ($erro !== ''): ?>
  <div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<div class="form-card">
  <form method="POST" action="pokemon_edit.php?id=<?= $pokemon->getId() ?>">

    <div class="form-group">
      <label for="nome">Título</label>
      <input
        type="text"
        id="nome"
        name="nome"
        placeholder="Ex: Charmander"
        value="<?= htmlspecialchars($nome) ?>"
        required
      />
    </div>

    <div class="form-group">
      <label for="tipo">Gênero</label>
      <select id="tipo" name="tipo" required>
        <option value="">Selecione o Gênero...</option>
        <?php foreach ($tipos as $t): ?>
          <?php
            $selecionado = '';
            if ($tipo === $t) {
                $selecionado = 'selected';
            }
          ?>
          <option value="<?= $t ?>" <?= $selecionado ?>>
            <?= $t ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="nivel">Nível (1 – 10)</label>
      <input
        type="number"
        id="nivel"
        name="nivel"
        min="0"
        max="10"
        step="0.1"
        value="<?= $nivel ?>"
        required
      />
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Salvar alterações</button>
      <a href="index.php" class="btn btn-ghost">Cancelar</a>
    </div>

  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>