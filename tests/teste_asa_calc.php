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

t_fim();
