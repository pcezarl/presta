<?php

// Periodo das telas de listagem (lista-boletos.php e lista-remessa.php).
// Rodar tambem num PHP 32 bits, como o XAMPP de producao:
//   docker run --rm --platform linux/386 -v "$PWD":/app -w /app php:5.6-cli \
//     php -d date.timezone=America/Sao_Paulo tests/teste_periodo.php

require_once dirname(__FILE__) . '/assert.php';
require_once dirname(__FILE__) . '/../lib/func.php';

echo "periodo :: PHP_INT_SIZE=" . PHP_INT_SIZE . "\n";

function resumo($p) {
    return "{$p['mes']}/{$p['ano']} dia={$p['dia']} [{$p['inicio']}, {$p['fim']})"
        . " ant={$p['mes_ant']}/{$p['ano_ant']} prox={$p['mes_prox']}/{$p['ano_prox']}";
}

// mes inteiro
t_equals('10/2026 dia=0 [2026-10-01, 2026-11-01) ant=9/2026 prox=11/2026',
    resumo(periodo_vencimento('10', '2026', '')), 'mes seguinte ao corrente');
t_equals('12/2026 dia=0 [2026-12-01, 2027-01-01) ant=11/2026 prox=1/2027',
    resumo(periodo_vencimento('12', '2026', '')), 'dezembro avanca o ano');
t_equals('1/2027 dia=0 [2027-01-01, 2027-02-01) ant=12/2026 prox=2/2027',
    resumo(periodo_vencimento('1', '2027', '')), 'janeiro volta o ano');
t_equals('9/2026 dia=0 [2026-09-01, 2026-10-01) ant=8/2026 prox=10/2026',
    resumo(periodo_vencimento('09', '2026', '')), 'mes com zero a esquerda');
t_equals('1/2040 dia=0 [2040-01-01, 2040-02-01) ant=12/2039 prox=2/2040',
    resumo(periodo_vencimento('1', '2040', '')), 'mes depois de 2038');

// dia dentro do mes
t_equals('10/2026 dia=5 [2026-10-05, 2026-10-06) ant=9/2026 prox=11/2026',
    resumo(periodo_vencimento('10', '2026', '5')), 'dia separado');
t_equals('10/2026 dia=5 [2026-10-05, 2026-10-06) ant=9/2026 prox=11/2026',
    resumo(periodo_vencimento('10', '2026', '05')), 'dia com zero a esquerda');
t_equals('10/2026 dia=31 [2026-10-31, 2026-11-01) ant=9/2026 prox=11/2026',
    resumo(periodo_vencimento('10', '2026', '31')), 'ultimo dia do mes');
t_equals('2/2028 dia=29 [2028-02-29, 2028-03-01) ant=1/2028 prox=3/2028',
    resumo(periodo_vencimento('2', '2028', '29')), '29 de fevereiro em ano bissexto');

// dia que nao existe no mes nao pode transbordar para o mes seguinte
t_equals('9/2026 dia=0 [2026-09-01, 2026-10-01) ant=8/2026 prox=10/2026',
    resumo(periodo_vencimento('9', '2026', '31')), '31 de setembro ignora o dia');
t_equals('2/2027 dia=0 [2027-02-01, 2027-03-01) ant=1/2027 prox=3/2027',
    resumo(periodo_vencimento('2', '2027', '29')), '29 de fevereiro fora do bissexto ignora o dia');
t_equals('10/2026 dia=0 [2026-10-01, 2026-11-01) ant=9/2026 prox=11/2026',
    resumo(periodo_vencimento('10', '2026', '0')), 'dia zero ignora o dia');
t_equals('10/2026 dia=0 [2026-10-01, 2026-11-01) ant=9/2026 prox=11/2026',
    resumo(periodo_vencimento('10', '2026', "1' or '1'='1")), 'dia com SQL ignora o dia');

// mes ou ano invalido cai no mes corrente
$mes_corrente = (int) date('n');
$ano_corrente = (int) date('Y');
$casos = array(
    'sem parametros'    => array('', '', ''),
    'mes 13'            => array('13', '2026', ''),
    'mes zero'          => array('0', '2026', ''),
    'ano fora da faixa' => array('10', '1999', ''),
    'mes com SQL'       => array("10' or '1'='1", '2026', ''),
    'ano com SQL'       => array('10', "2026' or '1'='1", ''),
    'so o mes'          => array('10', '', ''),
);
foreach ($casos as $label => $c) {
    $p = periodo_vencimento($c[0], $c[1], $c[2]);
    t_equals("$mes_corrente/$ano_corrente", "{$p['mes']}/{$p['ano']}", "$label usa o mes corrente");
    t_equals(date('Y-m-01'), $p['inicio'], "$label comeca no dia 1 do mes corrente");
}

t_fim();
