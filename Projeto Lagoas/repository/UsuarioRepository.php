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
