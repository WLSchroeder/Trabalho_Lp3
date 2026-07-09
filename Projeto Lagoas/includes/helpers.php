<?php

/**
 * Calcula a cor da nota (0 a 10) por faixas fixas:
 * vermelho (abaixo de 7) -> laranja (7 a 7.9) -> verde-claro (8 a 8.9) ->
 * verde-escuro (9 a 9.9) -> azul (10, nota máxima).
 */
function corNota(float $nota): string {
    $nota = max(0, min(10, $nota)); // garante que fica entre 0 e 10

    if ($nota >= 10) {
        return 'rgb(41, 128, 185)';   // azul — nota máxima
    }

    if ($nota >= 9) {
        return 'rgb(30, 132, 73)';    // verde-escuro (9.0 a 9.9)
    }

    if ($nota >= 8) {
        return 'rgb(102, 187, 106)';  // verde-claro (8.0 a 8.9)
    }

    if ($nota >= 7) {
        return 'rgb(243, 156, 18)';   // laranja (7.0 a 7.9)
    }

    return 'rgb(231, 76, 60)';        // vermelho (abaixo de 7)
}