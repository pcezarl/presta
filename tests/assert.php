<?php

$GLOBALS['t_total']  = 0;
$GLOBALS['t_falhas'] = 0;

function t_equals($esperado, $obtido, $label) {
    $GLOBALS['t_total']++;
    if ((string) $esperado === (string) $obtido) {
        echo "  OK    $label\n";
        return;
    }
    $GLOBALS['t_falhas']++;
    echo "  FALHA $label\n";
    echo "          esperado: [$esperado]\n";
    echo "          obtido:   [$obtido]\n";
}

function t_fim() {
    $total  = $GLOBALS['t_total'];
    $falhas = $GLOBALS['t_falhas'];
    echo "\n$total verificacoes, $falhas falha(s)\n";
    exit($falhas > 0 ? 1 : 0);
}
