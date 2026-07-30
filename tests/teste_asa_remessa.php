<?php

require_once dirname(__FILE__) . '/assert.php';
require_once dirname(__FILE__) . '/../lib/class_asa_calc.php';

chdir(dirname(__FILE__) . '/..');
require_once 'lib/func.php';
require_once 'lib/class_remessa_ASA.php';

$data = array(
    'documento'                => '00.000.000/0000-00',
    'nome_empresa'             => 'EMPRESA TESTE',
    'agencia'                  => '0001',
    'agencia_dv'               => '9',
    'conta_corrente'           => '600001425',
    'conta_dv'                 => '8',
    'operacao'                 => '0004142',
    'carteira'                 => '121',
    'numero_sequencia_remessa' => '1',
    'valor_titulo'             => '20.00',
    'data_vencimento_titulo'   => '2025-05-30',
    'data_emissao_titulo'      => '2025-04-10',
    'numero_documento'         => 'TESTE ASA',
    'nosso_numero'             => '465280',
    'documento_pagador'        => '111.222.333-44',
    'nome_pagador'             => 'PAGADOR TESTE',
    'endereco_pagador'         => 'RUA TESTE - 100',
    'bairro_pagador'           => 'CENTRO',
    'cidade_pagador'           => 'SAO PAULO',
    'estado_pagador'           => 'SP',
    'cep_pagador'              => '01000-000',
);

echo "remessa_ASA :: largura dos registros\n";

$r = new remessa_ASA();
$r->header($data);

$registros = array(
    'header de arquivo'  => $r->header_arquivo($data),
    'header de lote'     => $r->header_lote($data),
    'segmento P'         => $r->segmento_p($data),
    'segmento Q'         => $r->segmento_q($data),
    'segmento R'         => $r->segmento_r($data),
    'trailer de lote'    => $r->trailer_lote(),
    'trailer de arquivo' => $r->trailer_arquivo(),
);
foreach ($registros as $nome => $linha) {
    t_equals(240, strlen($linha), "$nome tem 240 posicoes");
}

echo "\nremessa_ASA :: posicoes fixas do segmento P\n";

$p = $r->segmento_p($data);
t_equals('594', substr($p, 0, 3),    'P: banco nas posicoes 1-3');
t_equals('3',   substr($p, 7, 1),    'P: tipo de registro na posicao 8');
t_equals('P',   substr($p, 13, 1),   'P: codigo do segmento na posicao 14');
t_equals('01',  substr($p, 15, 2),   'P: movimento entrada de titulos nas posicoes 16-17');
t_equals('00001', substr($p, 17, 5), 'P: agencia nas posicoes 18-22');
t_equals('9',   substr($p, 22, 1),   'P: DV da agencia na posicao 23');
t_equals('000600001425', substr($p, 23, 12), 'P: conta nas posicoes 24-35');
t_equals('8',   substr($p, 35, 1),   'P: DV da conta na posicao 36');
t_equals('2',   substr($p, 57, 1),   'P: carteira cobranca vinculada na posicao 58');
t_equals('2',   substr($p, 60, 1),   'P: emissao pelo cliente na posicao 61');
t_equals('30052025', substr($p, 77, 8), 'P: vencimento nas posicoes 78-85');
t_equals('000000000002000', substr($p, 85, 15), 'P: valor nas posicoes 86-100');
t_equals('02',  substr($p, 106, 2),  'P: especie DM nas posicoes 107-108');
t_equals('2',   substr($p, 117, 1),  'P: juros taxa mensal na posicao 118');
t_equals('000000000000100', substr($p, 126, 15), 'P: juros de 1,00% nas posicoes 127-141');
t_equals('3',   substr($p, 220, 1),  'P: nao protestar na posicao 221');
t_equals('09',  substr($p, 227, 2),  'P: moeda real nas posicoes 228-229');

echo "\nremessa_ASA :: segmentos Q e R\n";

$q = $r->segmento_q($data);
t_equals('Q',   substr($q, 13, 1),   'Q: codigo do segmento na posicao 14');
t_equals('1',   substr($q, 17, 1),   'Q: tipo de inscricao CPF na posicao 18');
t_equals('01000', substr($q, 128, 5),'Q: CEP nas posicoes 129-133');
t_equals('000', substr($q, 209, 3),  'Q: banco correspondente zerado nas posicoes 210-212');

$s = $r->segmento_r($data);
t_equals('R',   substr($s, 13, 1),   'R: codigo do segmento na posicao 14');
t_equals('2',   substr($s, 65, 1),   'R: multa percentual na posicao 66');
t_equals('000000000000200', substr($s, 74, 15), 'R: multa de 2,00% nas posicoes 75-89');

echo "\nremessa_ASA :: identificacao do titulo (posicoes 38-57)\n";

t_equals(20, strlen($r->identificacao_titulo('121', '0001', '465280')), 'identificacao do titulo tem 20 posicoes');
t_equals('12100000000004652809', $r->identificacao_titulo('121', '0001', '465280'), 'particao Bradesco');

// O banco confirmou em 2026-07-27: "o nosso numero com range + DV deve ser
// informado das colunas 46 a 57" — 12 posicoes, que e o que a particao Bradesco produz.
$id_faixa = $r->identificacao_titulo('121', '0001', '7862083');
t_equals('121',   substr($id_faixa, 0, 3), 'identificacao: carteira nas colunas 38-40');
t_equals('00000', substr($id_faixa, 3, 5), 'identificacao: zeros nas colunas 41-45');
t_equals('000078620831', substr($id_faixa, 8, 12), 'identificacao: NN da faixa + DV nas colunas 46-57');

// A mesma faixa dentro do segmento P completo, para garantir o alinhamento absoluto.
$dados_faixa = array_merge($data, array('nosso_numero' => '7862083'));
$p_faixa = $r->segmento_p($dados_faixa);
t_equals('000078620831', substr($p_faixa, 45, 12), 'segmento P: NN + DV nas posicoes 46-57 do registro');

t_fim();
