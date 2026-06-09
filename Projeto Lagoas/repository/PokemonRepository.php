<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entity/Pokemon.php';

class PokemonRepository {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = getConexao();
    }

    /** @return Pokemon[] */
    public function listarPorUsuario(int $usuarioId): array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM pokemon WHERE usuario_id = :uid ORDER BY nome ASC'
        );
        $stmt->execute([':uid' => $usuarioId]);
        $lista = [];
        foreach ($stmt->fetchAll() as $dados) {
            $lista[] = new Pokemon($dados);
        }
        return $lista;
    }

    public function buscarPorId(int $id): ?Pokemon {
        $stmt = $this->pdo->prepare('SELECT * FROM pokemon WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $dados = $stmt->fetch();

        if ($dados) {
            return new Pokemon($dados);
        }

        return null;
    }

    public function salvar(Pokemon $pokemon): void {
        if ($pokemon->getId() > 0) {
            $stmt = $this->pdo->prepare(
                'UPDATE pokemon SET nome = :nome, tipo = :tipo, nivel = :nivel WHERE id = :id'
            );
            $stmt->execute([
                ':nome'  => $pokemon->getNome(),
                ':tipo'  => $pokemon->getTipo(),
                ':nivel' => $pokemon->getNivel(),
                ':id'    => $pokemon->getId(),
            ]);
            return;
        }

        if ($pokemon->getUsuarioId() <= 0) {
            throw new InvalidArgumentException('Usuário inválido.');
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO pokemon (nome, tipo, nivel, usuario_id) VALUES (:nome, :tipo, :nivel, :uid)'
        );
        $stmt->execute([
            ':nome'  => $pokemon->getNome(),
            ':tipo'  => $pokemon->getTipo(),
            ':nivel' => $pokemon->getNivel(),
            ':uid'   => $pokemon->getUsuarioId(),
        ]);

        $pokemon->registrarIdGerado((int) $this->pdo->lastInsertId());
    }

    public function inserir(string $nome, string $tipo, int $nivel, int $usuarioId): void {
        $pokemon = Pokemon::novo($nome, $tipo, $nivel, $usuarioId);
        $this->salvar($pokemon);
    }

    public function atualizar(int $id, string $nome, string $tipo, int $nivel): void {
        $pokemon = $this->buscarPorId($id);

        if ($pokemon === null) {
            throw new RuntimeException('Pokémon não encontrado.');
        }

        $pokemon->alterarDados($nome, $tipo, $nivel);
        $this->salvar($pokemon);
    }

    public function excluir(int $id): void {
        $stmt = $this->pdo->prepare('DELETE FROM pokemon WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
