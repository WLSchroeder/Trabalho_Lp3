<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entity/Genero.php';

class GeneroRepository {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = getConexao();
    }

    /** @return Genero[] */
    public function listarTodos(): array {
        $stmt = $this->pdo->query('SELECT * FROM genero ORDER BY nome ASC');

        $lista = [];
        foreach ($stmt->fetchAll() as $dados) {
            $lista[] = new Genero($dados);
        }

        return $lista;
    }

    public function buscarPorId(int $id): ?Genero {
        $stmt = $this->pdo->prepare('SELECT * FROM genero WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $dados = $stmt->fetch();

        if ($dados) {
            return new Genero($dados);
        }

        return null;
    }
}
