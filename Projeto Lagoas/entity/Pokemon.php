<?php

class Pokemon {

    private int    $id;
    private string $nome;
    private string $tipo;
    private int    $nivel;
    private int    $usuarioId;

    public function __construct(array $dados) {
        $this->id        = (int) ($dados['id']        ?? 0);
        $this->nome      =        $dados['nome']       ?? '';
        $this->tipo      =        $dados['tipo']       ?? '';
        $this->nivel     = (int) ($dados['nivel']      ?? 1);
        $this->usuarioId = (int) ($dados['usuario_id'] ?? 0);
    }

    public function getId():        int    { return $this->id; }
    public function getNome():      string { return $this->nome; }
    public function getTipo():      string { return $this->tipo; }
    public function getNivel():     int    { return $this->nivel; }
    public function getUsuarioId(): int    { return $this->usuarioId; }

    public static function novo(string $nome, string $tipo, int $nivel, int $usuarioId): Pokemon {
        if ($usuarioId <= 0) {
            throw new InvalidArgumentException('Usuário inválido.');
        }

        $pokemon = new Pokemon(['usuario_id' => $usuarioId]);
        $pokemon->alterarDados($nome, $tipo, $nivel);

        return $pokemon;
    }

    public function alterarDados(string $nome, string $tipo, int $nivel): void {
        $nome = trim($nome);
        $tipo = trim($tipo);

        if ($nome === '' || $tipo === '') {
            throw new InvalidArgumentException('Nome e tipo são obrigatórios.');
        }

        if ($nivel < 1 || $nivel > 100) {
            throw new InvalidArgumentException('O nível deve ser entre 1 e 100.');
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
