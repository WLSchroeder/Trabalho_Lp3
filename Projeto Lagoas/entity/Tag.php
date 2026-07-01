<?php

class Tag {

    private int    $id;
    private string $nome;

    public function __construct(array $dados) {
        $this->id   = (int) ($dados['id']   ?? 0);
        $this->nome =        $dados['nome'] ?? '';
    }

    public function getId():   int    { return $this->id; }
    public function getNome(): string { return $this->nome; }

    public static function novo(string $nome): Tag {
        $tag = new Tag([]);
        $tag->alterarNome($nome);

        return $tag;
    }

    public function alterarNome(string $nome): void {
        $nome = trim($nome);

        if ($nome === '') {
            throw new InvalidArgumentException('O nome da tag é obrigatório.');
        }

        if (mb_strlen($nome) > 50) {
            throw new InvalidArgumentException('O nome da tag deve ter no máximo 50 caracteres.');
        }

        $this->nome = $nome;
    }

    public function registrarIdGerado(int $id): void {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID inválido.');
        }

        $this->id = $id;
    }
}
