<?php

class Pokemon {

    private int    $id;
    private string $nome;       // Título do filme ou série
    private string $tipo;       // Gênero
    private float  $nivel;      // Nota (1 a 10)
    private int    $usuarioId;

    public function __construct(array $dados) {
        $this->id        = (int) ($dados['id']        ?? 0);
        $this->nome      =        $dados['nome']       ?? '';
        $this->tipo      =        $dados['tipo']       ?? '';
        $this->nivel = (float) ($dados['nivel'] ?? 1.0);
        $this->usuarioId = (int) ($dados['usuario_id'] ?? 0);
    }

    public function getId():        int    { return $this->id; }
    public function getNome():      string { return $this->nome; }
    public function getTipo():      string { return $this->tipo; }
    public function getNivel():     float    { return $this->nivel; }
    public function getUsuarioId(): int    { return $this->usuarioId; }

    public static function novo(string $nome, string $tipo, float $nivel, int $usuarioId): Pokemon {
        if ($usuarioId <= 0) {
            throw new InvalidArgumentException('Usuário inválido.');
        }

        $pokemon = new Pokemon(['usuario_id' => $usuarioId]);
        $pokemon->alterarDados($nome, $tipo, $nivel);

        return $pokemon;
    }

    public function alterarDados(string $nome, string $tipo, float $nivel): void {
        $nome = trim($nome);
        $tipo = trim($tipo);

        if ($nome === '' || $tipo === '') {
            throw new InvalidArgumentException('Título e Gênero são obrigatórios.');
        }

        if ($nivel < 0 || $nivel > 10) {
            throw new InvalidArgumentException('A nota deve ser entre 1 e 10.');
        }

        $this->nome  = $nome;
        $this->tipo  = $tipo;
        $this->nivel = $nivel;
    }

    public function registrarIdGerado(int $id): void {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID inválido.');
        }

        $this->id = $id;
    }
}
