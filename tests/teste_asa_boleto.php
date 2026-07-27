<?php

require_once dirname(__FILE__) . '/assert.php';
require_once dirname(__FILE__) . '/../lib/class_asa_calc.php';

// A classe carrega o layout por caminho relativo a raiz do projeto.
chdir(dirname(__FILE__) . '/..');
require_once 'lib/class_boleto_ASA.php';

echo "boleto_ASA :: renderizacao com o vetor de aceitacao\n";

$b = new boleto_ASA();
$b->val('agencia',          '0001');
$b->val('agencia_dv',       '9');
$b->val('conta',            '600001425');
$b->val('conta_dv',         '8');
$b->val('operacao',         '0001316');
$b->val('carteira',         '121');
$b->val('nosso_numero',     '465280');
$b->val('numero_documento', 'TESTE ASA');
$b->val('valor_boleto',     '20.00');
$b->val('data_vencimento',  '30/05/2025');
$b->val('razao',            'EMPRESA TESTE');
$b->val('cnpj',             '00.000.000/0000-00');

// Tokens preenchidos por proc_lista_boletos.php, nao pela classe.
$b->set('demonstrativo1', 'Parcela 1 (MENSALIDADE)');
$b->set('demonstrativo2', 'Edificio Teste - apartamento 101');
$b->set('endereco1',      'Rua Teste, 100 - Centro');
$b->set('endereco2',      'Sao Paulo - SP - CEP: 01000-000');
$b->set('sacado',         'PAGADOR TESTE - CPF/CNPJ: 111.222.333-44');
$b->set('data_documento', '10/04/2025');

$b->draw();

t_equals('00004652809', $b->dadosboleto['nosso_numero_completo'], 'nosso numero com DV no dadosboleto');
t_equals(
    '59490.00110 21000.131603 00046.528097 5 10970000002000',
    $b->dadosboleto['linha_digitavel'],
    'linha digitavel no dadosboleto'
);

t_equals(0, substr_count($b->layout, '{{'), 'nenhum token nao substituido no layout');
t_equals(true, strpos($b->layout, '0465280-9') !== false, 'nosso numero formatado aparece no layout');
t_equals(true, strpos($b->layout, '0001-9 / 600001425-8') !== false, 'agencia/codigo do beneficiario aparece no layout');
t_equals(true, strpos($b->layout, '594-0') !== false, 'codigo do banco com DV aparece no layout');

echo "\nboleto_ASA :: reset e reuso entre boletos\n";

$b->reset();
t_equals(true, strpos($b->layout, '{{linha_digitavel}}') !== false, 'reset restaura os tokens do layout');

t_fim();
