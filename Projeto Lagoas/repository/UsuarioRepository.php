<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../entity/Usuario.php';

class UsuarioRepository {

    private PDO $pdo;

    public function __construct() {
        $this->pdo = getConexao();
    }

    public function buscarPorEmail(string $email): ?Usuario {
        $stmt = $this->pdo->prepare('SELECT * FROM usuario WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $dados = $stmt->fetch();

        if ($dados) {
            return new Usuario($dados);
        }

        return null;
    }

    /**
     * Lista todos os usuários com a quantidade de filmes/séries que cada
     * um já avaliou (cadastrou), ordenados do que avaliou mais para o
     * que avaliou menos. Usado para montar o ranking de níveis.
     *
     * @return array<int, array{id:int, nome:string, totalAvaliados:int}>
     */
    public function listarRanking(): array {
        $stmt = $this->pdo->query(
            'SELECT u.id, u.nome, COUNT(f.id) AS total_avaliados
             FROM usuario u
             LEFT JOIN filme f ON f.usuario_id = u.id
             GROUP BY u.id, u.nome
             ORDER BY total_avaliados DESC, u.nome ASC'
        );

        $lista = [];
        foreach ($stmt->fetchAll() as $linha) {
            $lista[] = [
                'id'             => (int) $linha['id'],
                'nome'           => $linha['nome'],
                'totalAvaliados' => (int) $linha['total_avaliados'],
            ];
        }

        return $lista;
    }

    public function existeComEmail(string $email): bool {
        $stmt = $this->pdo->prepare('SELECT id FROM usuario WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);

        return $stmt->fetch() !== false;
    }

    public function salvar(Usuario $usuario): void {
        if ($this->existeComEmail($usuario->getEmail())) {
            throw new InvalidArgumentException('Já existe uma conta cadastrada com esse e-mail.');
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO usuario (nome, email, senha) VALUES (:nome, :email, :senha)'
        );

        $stmt->execute([
            ':nome'  => $usuario->getNome(),
            ':email' => $usuario->getEmail(),
            ':senha' => $usuario->getSenha(),
        ]);

        $usuario->registrarIdGerado((int) $this->pdo->lastInsertId());
    }
}
