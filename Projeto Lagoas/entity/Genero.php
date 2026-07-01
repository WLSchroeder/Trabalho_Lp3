<?php

class Genero {

    private int    $id;
    private string $nome;

    public function __construct(array $dados) {
        $this->id   = (int) ($dados['id']   ?? 0);
        $this->nome =        $dados['nome'] ?? '';
    }

    public function getId():   int    { return $this->id; }
    public function getNome(): string { return $this->nome; }
}
