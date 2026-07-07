<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entity/Filme.php';
require_once __DIR__ . '/../entity/Tag.php';

class FilmeRepository {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = getConexao();
    }

    private const SELECT_BASE = '
        SELECT f.*, g.nome AS genero_nome
        FROM filme f
        JOIN genero g ON g.id = f.genero_id
    ';

    /**
     * Lista os filmes/séries de um usuário, ordenados pela nota (nivel).
     *
     * @param int    $usuarioId
     * @param string $ordemNota 'asc' para nota crescente, 'desc' para nota decrescente (padrão)
     * @return Filme[]
     */
    public function listarPorUsuario(int $usuarioId, string $ordemNota = 'desc'): array {
        $ordemNota = strtolower($ordemNota) === 'asc' ? 'ASC' : 'DESC';

        $stmt = $this->pdo->prepare(
            self::SELECT_BASE . " WHERE f.usuario_id = :uid ORDER BY f.nivel {$ordemNota}, f.nome ASC"
        );
        $stmt->execute([':uid' => $usuarioId]);

        $lista = [];
        foreach ($stmt->fetchAll() as $dados) {
            $lista[] = new Filme($dados);
        }

        return $lista;
    }

    public function buscarPorId(int $id, int $usuarioId): ?Filme {
        $stmt = $this->pdo->prepare(
            self::SELECT_BASE . ' WHERE f.id = :id AND f.usuario_id = :uid LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':uid' => $usuarioId]);

        $dados = $stmt->fetch();

        if ($dados) {
            return new Filme($dados);
        }

        return null;
    }

    public function salvar(Filme $filme): void {
        // Se já possui ID, atualiza o registro
        if ($filme->getId() > 0) {
            $stmt = $this->pdo->prepare(
                'UPDATE filme
                 SET nome = :nome,
                     genero_id = :genero_id,
                     nivel = :nivel,
                     imagem = :imagem
                 WHERE id = :id'
            );

            $stmt->execute([
                ':nome'      => $filme->getNome(),
                ':genero_id' => $filme->getGeneroId(),
                ':nivel'     => $filme->getNivel(),
                ':imagem'    => $filme->getImagem(),
                ':id'        => $filme->getId(),
            ]);

            return;
        }

        // Se não possui ID, insere um novo registro
        if ($filme->getUsuarioId() <= 0) {
            throw new InvalidArgumentException('Usuário inválido.');
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO filme
             (nome, genero_id, nivel, imagem, usuario_id)
             VALUES
             (:nome, :genero_id, :nivel, :imagem, :uid)'
        );

        $stmt->execute([
            ':nome'      => $filme->getNome(),
            ':genero_id' => $filme->getGeneroId(),
            ':nivel'     => $filme->getNivel(),
            ':imagem'    => $filme->getImagem(),
            ':uid'       => $filme->getUsuarioId(),
        ]);

        $filme->registrarIdGerado(
            (int) $this->pdo->lastInsertId()
        );
    }

    public function inserir(
        string $nome,
        int $generoId,
        float $nivel,
        int $usuarioId
    ): void {
        $filme = Filme::novo($nome, $generoId, $nivel, $usuarioId);
        $this->salvar($filme);
    }

    public function atualizar(
        int $id,
        string $nome,
        int $generoId,
        float $nivel,
        int $usuarioId
    ): void {
        $filme = $this->buscarPorId($id, $usuarioId);

        if ($filme === null) {
            throw new RuntimeException('Filme ou série não encontrado.');
        }

        $filme->alterarDados($nome, $generoId, $nivel);
        $this->salvar($filme);
    }

    public function excluir(int $id): void {
        $stmt = $this->pdo->prepare(
            'DELETE FROM filme WHERE id = :id'
        );

        $stmt->execute([':id' => $id]);
    }

    // ---------------------------------------------------------
    // Relacionamento N:N com tag (tabela intermediária filme_tag)
    // ---------------------------------------------------------

    /** @return Tag[] */
    public function buscarTagsDoFilme(int $filmeId): array {
        $stmt = $this->pdo->prepare(
            'SELECT t.*
             FROM tag t
             JOIN filme_tag ft ON ft.tag_id = t.id
             WHERE ft.filme_id = :filme_id
             ORDER BY t.nome ASC'
        );
        $stmt->execute([':filme_id' => $filmeId]);

        $lista = [];
        foreach ($stmt->fetchAll() as $dados) {
            $lista[] = new Tag($dados);
        }

        return $lista;
    }

    /**
     * Substitui todas as tags associadas a um filme pela lista informada.
     * @param int[] $tagIds
     */
    public function salvarTagsDoFilme(int $filmeId, array $tagIds): void {
        $stmtDelete = $this->pdo->prepare('DELETE FROM filme_tag WHERE filme_id = :filme_id');
        $stmtDelete->execute([':filme_id' => $filmeId]);

        if (empty($tagIds)) {
            return;
        }

        $stmtInsert = $this->pdo->prepare(
            'INSERT INTO filme_tag (filme_id, tag_id) VALUES (:filme_id, :tag_id)'
        );

        foreach ($tagIds as $tagId) {
            $tagId = (int) $tagId;
            if ($tagId > 0) {
                $stmtInsert->execute([
                    ':filme_id' => $filmeId,
                    ':tag_id'   => $tagId,
                ]);
            }
        }
    }
}
