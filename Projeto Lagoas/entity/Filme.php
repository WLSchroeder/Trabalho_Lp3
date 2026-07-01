<?php

class Filme {

    private int    $id;
    private string $nome;        // Título do filme ou série
    private int    $generoId;    // FK para genero.id
    private ?string $generoNome; // Preenchido via JOIN no Repository (somente leitura)
    private float  $nivel;       // Nota (0 a 10)
    private int    $usuarioId;

    public function __construct(array $dados) {
        $this->id         = (int) ($dados['id']         ?? 0);
        $this->nome        =        $dados['nome']        ?? '';
        $this->generoId   = (int) ($dados['genero_id']  ?? 0);
        $this->generoNome =        $dados['genero_nome'] ?? null;
        $this->nivel      = (float) ($dados['nivel']     ?? 1.0);
        $this->usuarioId  = (int) ($dados['usuario_id']  ?? 0);
    }

    public function getId():         int     { return $this->id; }
    public function getNome():       string  { return $this->nome; }
    public function getGeneroId():   int     { return $this->generoId; }
    public function getGeneroNome(): string  { return $this->generoNome ?? ''; }
    public function getNivel():      float   { return $this->nivel; }
    public function getUsuarioId():  int     { return $this->usuarioId; }

    public static function novo(string $nome, int $generoId, float $nivel, int $usuarioId): Filme {
        if ($usuarioId <= 0) {
            throw new InvalidArgumentException('Usuário inválido.');
        }

        $filme = new Filme(['usuario_id' => $usuarioId]);
        $filme->alterarDados($nome, $generoId, $nivel);

        return $filme;
    }

    public function alterarDados(string $nome, int $generoId, float $nivel): void {
        $nome = trim($nome);

        if ($nome === '') {
            throw new InvalidArgumentException('O título é obrigatório.');
        }

        if ($generoId <= 0) {
            throw new InvalidArgumentException('Selecione um gênero.');
        }

        if ($nivel < 0 || $nivel > 10) {
            throw new InvalidArgumentException('A nota deve ser entre 0 e 10.');
        }

        $this->nome      = $nome;
        $this->generoId = $generoId;
        $this->nivel     = $nivel;
    }

    public function registrarIdGerado(int $id): void {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID inválido.');
        }

        $this->id = $id;
    }
}
