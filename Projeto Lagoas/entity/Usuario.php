<?php

class Usuario {

    private int    $id;
    private string $nome;
    private string $email;
    private string $senha;
    private string $criadoEm;

    public function __construct(array $dados) {
        $this->id       = (int) ($dados['id']       ?? 0);
        $this->nome     =        $dados['nome']      ?? '';
        $this->email    =        $dados['email']     ?? '';
        $this->senha    =        $dados['senha']     ?? '';
        $this->criadoEm =        $dados['criado_em'] ?? '';
    }

    public function getId():       int    { return $this->id; }
    public function getNome():     string { return $this->nome; }
    public function getEmail():    string { return $this->email; }
    public function getSenha():    string { return $this->senha; }
    public function getCriadoEm(): string { return $this->criadoEm; }

    /**
     * Cria um novo usuário já validado, com a senha convertida pro hash
     * SHA256 (mesmo formato comparado no login.php).
     */
    public static function novo(string $nome, string $email, string $senha, string $confirmarSenha): Usuario {
        $nome  = trim($nome);
        $email = trim($email);

        if ($nome === '') {
            throw new InvalidArgumentException('O nome é obrigatório.');
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Informe um e-mail válido.');
        }

        if (mb_strlen($senha) < 6) {
            throw new InvalidArgumentException('A senha deve ter no mínimo 6 caracteres.');
        }

        if ($senha !== $confirmarSenha) {
            throw new InvalidArgumentException('As senhas não coincidem.');
        }

        return new Usuario([
            'nome'  => $nome,
            'email' => $email,
            'senha' => hash('sha256', $senha),
        ]);
    }

    public function registrarIdGerado(int $id): void {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID inválido.');
        }

        $this->id = $id;
    }
}
