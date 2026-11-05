<?php

// Regras do cadastro de contas (lib/func_conta.php).
// docker exec presta-app-1 php tests/teste_conta.php

chdir(dirname(__FILE__).'/..');
require 'tests/assert.php';
require 'lib/func_conta.php';

function base($banco, $extra = array()) {
	return array_merge(array(
		'banco' => $banco, 'razao' => 'EMPRESA X', 'cnpj' => '12.345.678/0001-90',
		'agencia' => '4321', 'conta' => '123456',
	), $extra);
}

echo "SICOOB sem faixa assume o intervalo inteiro\n";
$r = conta_valida(base('SICOOB', array('cod_cliente' => '1234567')));
t_equals(0, count($r['erros']), 'sem erros');
t_equals('1', $r['dados']['faixa_inicio'], 'inicio = 1');
t_equals('9999999', $r['dados']['faixa_fim'], 'fim = 9999999');

echo "SICOOB com faixa parcial completa so o que falta\n";
$r = conta_valida(base('SICOOB', array('cod_cliente' => '1', 'faixa_inicio' => '3000000')));
t_equals('3000000', $r['dados']['faixa_inicio'], 'inicio mantido');
t_equals('9999999', $r['dados']['faixa_fim'], 'fim = max');

echo "SICOOB fora do limite do banco\n";
$r = conta_valida(base('SICOOB', array('cod_cliente' => '1', 'faixa_fim' => '10000000')));
t_equals(1, count($r['erros']), 'acusa faixa > 7 digitos');

echo "SICOOB exige codigo do cliente\n";
$r = conta_valida(base('SICOOB'));
t_equals(1, count($r['erros']), 'acusa cod_cliente');

echo "ASA exige operacao, carteira, digitos e faixa\n";
$r = conta_valida(base('ASA'));
t_equals(4, count($r['erros']), '4 erros');
$r = conta_valida(base('ASA', array('operacao' => '0004254', 'carteira' => '121', 'agencia_dv' => '0',
	'conta_dv' => '0', 'faixa_inicio' => '8502083', 'faixa_fim' => '8517082')));
t_equals(0, count($r['erros']), 'completo passa');
t_equals('0004254', $r['dados']['operacao'], 'zeros a esquerda da operacao mantidos');

echo "ASA com faixa invertida\n";
$r = conta_valida(base('ASA', array('operacao' => '1', 'carteira' => '121', 'agencia_dv' => '0',
	'conta_dv' => '0', 'faixa_inicio' => '9', 'faixa_fim' => '1')));
t_equals(1, count($r['erros']), 'acusa faixa invertida');

echo "Campos de outro banco sao descartados\n";
$r = conta_valida(base('BRA', array('acessorio' => '999', 'operacao' => '123', 'faixa_inicio' => '5', 'cod_cliente' => '7')));
t_equals(0, count($r['erros']), 'sem erros');
t_equals('', $r['dados']['operacao'], 'operacao limpa');
t_equals('', $r['dados']['faixa_inicio'], 'faixa limpa');
t_equals('', $r['dados']['cod_cliente'], 'cod_cliente limpo');

echo "Bradesco exige codigo da empresa\n";
$r = conta_valida(base('BRA'));
t_equals(1, count($r['erros']), 'acusa acessorio');

echo "Comuns\n";
$r = conta_valida(array('banco' => 'CEF'));
t_equals(4, count($r['erros']), 'razao, cnpj, agencia, conta');
$r = conta_valida(array('banco' => 'XYZ'));
t_equals(1, count($r['erros']), 'banco invalido');
$r = conta_valida(base('CEF', array('razao' => "O'Brien <b>Ltda</b>")));
t_equals('OBrien Ltda', $r['dados']['razao'], 'aspas e tags removidas');

t_fim();
