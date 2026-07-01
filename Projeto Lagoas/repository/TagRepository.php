<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entity/Tag.php';

class TagRepository {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = getConexao();
    }

    /** @return Tag[] */
    public function listarTodas(): array {
        $stmt = $this->pdo->query('SELECT * FROM tag ORDER BY nome ASC');

        $lista = [];
        foreach ($stmt->fetchAll() as $dados) {
            $lista[] = new Tag($dados);
        }

        return $lista;
    }

    public function buscarPorId(int $id): ?Tag {
        $stmt = $this->pdo->prepare('SELECT * FROM tag WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $dados = $stmt->fetch();

        if ($dados) {
            return new Tag($dados);
        }

        return null;
    }

    private function existeComMesmoNome(string $nome, int $idIgnorar = 0): bool {
        $stmt = $this->pdo->prepare(
            'SELECT id FROM tag WHERE nome = :nome AND id != :id LIMIT 1'
        );
        $stmt->execute([':nome' => $nome, ':id' => $idIgnorar]);

        return $stmt->fetch() !== false;
    }

    public function salvar(Tag $tag): void {
        if ($this->existeComMesmoNome($tag->getNome(), $tag->getId())) {
            throw new InvalidArgumentException('Já existe uma tag com esse nome.');
        }

        if ($tag->getId() > 0) {
            $stmt = $this->pdo->prepare('UPDATE tag SET nome = :nome WHERE id = :id');
            $stmt->execute([
                ':nome' => $tag->getNome(),
                ':id'   => $tag->getId(),
            ]);

            return;
        }

        $stmt = $this->pdo->prepare('INSERT INTO tag (nome) VALUES (:nome)');
        $stmt->execute([':nome' => $tag->getNome()]);

        $tag->registrarIdGerado((int) $this->pdo->lastInsertId());
    }

    public function excluir(int $id): void {
        $stmt = $this->pdo->prepare('DELETE FROM tag WHERE id = :id');
        $stmt->execute([':id' => $id]);
        // filme_tag tem ON DELETE CASCADE, então os vínculos somem junto
    }
}
