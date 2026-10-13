<?php

// Rodar tambem num PHP 32 bits, como o XAMPP de producao, para pegar o estouro de 2038:
//   docker run --rm --platform linux/386 -v "$PWD":/app -w /app php:5.6-cli \
//     php -d date.timezone=America/Sao_Paulo tests/teste_datas.php

require_once dirname(__FILE__) . '/assert.php';
require_once dirname(__FILE__) . '/../lib/func.php';

echo "datas :: PHP_INT_SIZE=" . PHP_INT_SIZE . "\n";

t_equals('2030-06-10', data_soma_meses(2030, 6, 10, 0), 'primeira parcela e a data inicial');
t_equals('2038-01-10', data_soma_meses(2030, 6, 10, 91), 'ultima parcela antes do estouro');
t_equals('2038-02-10', data_soma_meses(2030, 6, 10, 92), 'primeira parcela depois de 19/01/2038');
t_equals('2060-05-10', data_soma_meses(2030, 6, 10, 359), '360 parcelas');
t_equals('2026-03-03', data_soma_meses(2026, 1, 31, 1), 'dia 31 transborda como o strtotime');

t_equals('15/01/2040', mydata('2040-01-15'), 'mydata depois de 2038');
t_equals('2040-01-15', data2mysql('15/01/2040'), 'data2mysql depois de 2038');
t_equals('10/06/2030', mydata('2030-06-10'), 'mydata antes de 2038');
t_equals('2030-06-10', data2mysql('10/06/2030'), 'data2mysql antes de 2038');

// parcelas de entrada/semestral/trimestral/intermediaria, como o prep_presta.php guarda
t_equals('2045-07-05', formata_data('2045-7-5', 'Y-m-d'), 'parcela avulsa depois de 2038');
t_equals('2030-06-10', formata_data(mktime(0, 0, 0, 6, 10, 2030), 'Y-m-d'), 'timestamp de sessao antiga');

t_fim();
