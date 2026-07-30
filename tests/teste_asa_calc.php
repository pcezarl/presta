<?php

require_once dirname(__FILE__) . '/assert.php';
require_once dirname(__FILE__) . '/../lib/class_asa_calc.php';

echo "asa_calc :: vetor de aceitacao do boleto-modelo\n";

$agencia    = '0001';
$carteira   = '121';
$operacao   = '0001316';
$nn         = '465280';
$vencimento = '30/05/2025';
$valor      = '20.00';

t_equals(9, asa_calc::dv_nosso_numero($agencia, $carteira, $nn), 'DV do nosso numero');
t_equals('00004652809', asa_calc::nosso_numero_com_dv($agencia, $carteira, $nn), 'nosso numero com DV');

$campo_livre = asa_calc::campo_livre($agencia, $carteira, $operacao, $nn);
t_equals('0001121000131600004652809', $campo_livre, 'campo livre');
t_equals(25, strlen($campo_livre), 'campo livre tem 25 posicoes');

$fator = asa_calc::fator_vencimento($vencimento);
t_equals('1097', $fator, 'fator de vencimento');

$valor10 = asa_calc::valor_centavos($valor, 10);
t_equals('0000002000', $valor10, 'valor em centavos com 10 posicoes');

$dv_barras = asa_calc::dv_codigo_barras($campo_livre, $fator, $valor10);
t_equals(5, $dv_barras, 'DV do codigo de barras');

$barras = asa_calc::codigo_barras($campo_livre, $fator, $valor10);
t_equals('59495109700000020000001121000131600004652809', $barras, 'codigo de barras');
t_equals(44, strlen($barras), 'codigo de barras tem 44 posicoes');

t_equals(
    '59490.00110 21000.131603 00046.528097 5 10970000002000',
    asa_calc::linha_digitavel($campo_livre, $fator, $valor10, $dv_barras),
    'linha digitavel'
);

echo "\nasa_calc :: casos de borda\n";

t_equals('1000', asa_calc::fator_vencimento('22/02/2025'), 'fator na data-base nova');
t_equals('9999', asa_calc::fator_vencimento('21/02/2025'), 'fator no limite da data-base antiga');
t_equals('1000', asa_calc::fator_vencimento('03/07/2000'), 'fator no inicio da data-base antiga');
t_equals('000000000000123456', asa_calc::valor_centavos('1234.56', 18), 'valor com truncamento de ponto flutuante');
t_equals('0000000000', asa_calc::valor_centavos('0', 10), 'valor zero');

echo "\nasa_calc :: confirmacoes do banco (resposta de 2026-07-27)\n";

// Planilha de calculo do DV enviada pelo banco: AG 0001, carteira 121, NN 1 -> digito 1.
t_equals(1, asa_calc::dv_nosso_numero('0001', '121', '1'), 'DV do exemplo da planilha do banco');
t_equals('00000000011', asa_calc::nosso_numero_com_dv('0001', '121', '1'), 'NN com digito do exemplo da planilha');

// Faixa liberada para a conta: 0007862083 a 0007877082.
t_equals(1, asa_calc::dv_nosso_numero('0001', '121', '7862083'), 'DV do primeiro NN da faixa');
t_equals(6, asa_calc::dv_nosso_numero('0001', '121', '7877082'), 'DV do ultimo NN da faixa');

// O banco confirmou que a operacao entra somente na linha digitavel, campos 13 a 19.
$cl_real    = asa_calc::campo_livre('0001', '121', '0004142', '7862083');
$fator_real = asa_calc::fator_vencimento('30/05/2025');
$v10_real   = asa_calc::valor_centavos('20.00', 10);
$ld_real    = asa_calc::linha_digitavel($cl_real, $fator_real, $v10_real, asa_calc::dv_codigo_barras($cl_real, $fator_real, $v10_real));
$digitos    = preg_replace('/[^0-9]/', '', $ld_real);

t_equals(47, strlen($digitos), 'linha digitavel tem 47 digitos');
t_equals('0004142', substr($digitos, 12, 7), 'operacao nos campos 13 a 19 da linha digitavel');

t_fim();
