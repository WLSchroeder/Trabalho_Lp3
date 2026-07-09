<?php

/**
 * Serviço responsável por calcular o nível de "cinefilia" do usuário
 * a partir da quantidade de filmes/séries que ele já avaliou (cadastrou).
 *
 * Regra base: a cada 5 filmes avaliados o usuário sobe 1 nível.
 * A partir do nível 5, a progressão fica mais exigente (o usuário
 * precisa avaliar mais filmes para subir), simulando uma curva de
 * experiência parecida com a de jogos.
 *
 * O nível NÃO é armazenado no banco: ele é sempre recalculado com base
 * na contagem atual de filmes do usuário, então nunca fica desatualizado.
 */
class NivelService {

    /**
     * Tabela de níveis: cada item representa a quantidade MÍNIMA de
     * filmes avaliados necessária para alcançar aquele nível.
     *
     * @return array<int, array{nivel:int, titulo:string, minimo:int, icone:string}>
     */
    private static function tabelaDeNiveis(): array {
        return [
            ['nivel' => 1,  'titulo' => 'Espectador Casual',        'minimo' => 0,   'icone' => '🍿'],
            ['nivel' => 2,  'titulo' => 'Cinéfilo Iniciante',       'minimo' => 5,   'icone' => '🎬'],
            ['nivel' => 3,  'titulo' => 'Apreciador de Cinema',     'minimo' => 10,  'icone' => '🎞️'],
            ['nivel' => 4,  'titulo' => 'Maratonista de Séries',    'minimo' => 20,  'icone' => '📺'],
            ['nivel' => 5,  'titulo' => 'Crítico Amador',           'minimo' => 35,  'icone' => '📝'],
            ['nivel' => 6,  'titulo' => 'Crítico Renomado',         'minimo' => 55,  'icone' => '⭐'],
            ['nivel' => 7,  'titulo' => 'Especialista em Cinema',   'minimo' => 80,  'icone' => '🏆'],
            ['nivel' => 8,  'titulo' => 'Guru Cinematográfico',     'minimo' => 110, 'icone' => '🎩'],
            ['nivel' => 9,  'titulo' => 'Mestre da Sétima Arte',    'minimo' => 150, 'icone' => '👑'],
            ['nivel' => 10, 'titulo' => 'Lenda do CineRank',        'minimo' => 200, 'icone' => '🌟'],
        ];
    }

    /**
     * Calcula todas as informações de nível do usuário a partir da
     * quantidade de filmes/séries que ele já avaliou.
     *
     * @param int $totalAvaliados Quantidade de filmes/séries cadastrados pelo usuário
     * @return array{
     *     nivel:int,
     *     titulo:string,
     *     icone:string,
     *     totalAvaliados:int,
     *     minimoAtual:int,
     *     minimoProximo:?int,
     *     faltamParaProximo:?int,
     *     progresso:float,
     *     nivelMaximo:bool
     * }
     */
    public static function calcular(int $totalAvaliados): array {
        $tabela = self::tabelaDeNiveis();
        $totalAvaliados = max(0, $totalAvaliados);

        $atual = $tabela[0];
        $proximo = null;

        foreach ($tabela as $indice => $linha) {
            if ($totalAvaliados >= $linha['minimo']) {
                $atual = $linha;
                $proximo = $tabela[$indice + 1] ?? null;
            }
        }

        $nivelMaximo = $proximo === null;

        if ($nivelMaximo) {
            $progresso = 100.0;
            $faltam = null;
            $minimoProximo = null;
        } else {
            $faixa = $proximo['minimo'] - $atual['minimo'];
            $andados = $totalAvaliados - $atual['minimo'];
            $progresso = $faixa > 0 ? min(100.0, round(($andados / $faixa) * 100, 1)) : 100.0;
            $faltam = max(0, $proximo['minimo'] - $totalAvaliados);
            $minimoProximo = $proximo['minimo'];
        }

        return [
            'nivel'             => $atual['nivel'],
            'titulo'            => $atual['titulo'],
            'icone'             => $atual['icone'],
            'totalAvaliados'    => $totalAvaliados,
            'minimoAtual'       => $atual['minimo'],
            'minimoProximo'     => $minimoProximo,
            'faltamParaProximo' => $faltam,
            'progresso'         => $progresso,
            'nivelMaximo'       => $nivelMaximo,
            'proximoTitulo'     => $proximo['titulo'] ?? null,
        ];
    }
}
