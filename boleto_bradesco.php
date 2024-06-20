<?php
/*
// +----------------------------------------------------------------------+
// | BoletoPhp - Versão Beta                                              |
// +----------------------------------------------------------------------+
// | Este arquivo está disponível sob a Licença GPL disponível pela Web   |
// | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
// | Você deve ter recebido uma cópia da GNU Public License junto com     |
// | esse pacote; se não, escreva para:                                   |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// +----------------------------------------------------------------------+
// | Originado do Projeto BBBoletoFree que tiveram colaborações de Daniel |
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do	  |
// | PHPBoleto de João Prado Maia e Pablo Martins F. Costa			        |
// | 																	                    |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+
// +----------------------------------------------------------------------+
// | Equipe Coordenação Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenvolvimento Boleto Bradesco: Ramon Soares						        |
// +----------------------------------------------------------------------+
// ------------------------- DADOS DINÂMICOS DO SEU CLIENTE PARA A GERAÇÃO DO BOLETO (FIXO OU VIA GET) -------------------- //
// Os valores abaixo podem ser colocados manualmente ou ajustados p/ formulário c/ POST, GET ou de BD (MySql,Postgre,etc)	//
// DADOS DO BOLETO PARA O SEU CLIENTE
*/

$dias_de_prazo_para_pagamento = 5;
$taxa_boleto = 0;
$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006";
$valor_cobrado = "29,99"; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado = str_replace(",", ".",$valor_cobrado);
$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');
$dadosboleto["nosso_numero"] = "000000010";  // Nosso numero sem o DV - REGRA: Máximo de 11 caracteres!
$dadosboleto["numero_documento"] = $dadosboleto["nosso_numero"];	// Num do pedido ou do documento = Nosso numero
$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula
// DADOS DO SEU CLIENTE
$dadosboleto["sacado"]    = "Paulo Cézar Lima - ";
$dadosboleto["sacado"]   .= "CPF/CNPJ: 382.422.328-73";
$dadosboleto["endereco1"] = "Al. Araguaia - 2190 - Alphaville";
$dadosboleto["endereco2"] = "Barueri - São Paulo -  CEP: 04665-000";
// INFORMACOES PARA O CLIENTE
$dadosboleto["demonstrativo1"] = "Parcela 1 de 1";
$dadosboleto["demonstrativo2"] = "Edificio Teste<br>Taxa bancária - R$ ".number_format($taxa_boleto, 2, ',', '');
$dadosboleto["demonstrativo3"] = "";

$dadosboleto["instrucoes1"] = "";
$dadosboleto["instrucoes2"] = "Sr. Caixa, cobrar multa de 10% após o vencimento";
$dadosboleto["instrucoes3"] = "Não receber após 30 dias do vencimento";
$dadosboleto["instrucoes4"] = "";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "001";
$dadosboleto["valor_unitario"] = $valor_boleto;
$dadosboleto["aceite"] = "";
$dadosboleto["especie"] = "R$";
$dadosboleto["especie_doc"] = "DS";
// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
// DADOS DA SUA CONTA - Bradesco
$dadosboleto["agencia"] = "3399"; // Num da agencia, sem digito
$dadosboleto["agencia_dv"] = "5"; // Digito do Num da agencia
$dadosboleto["conta"] = "627"; 	// Num da conta, sem digito
$dadosboleto["conta_dv"] = "0"; 	// Digito do Num da conta
// DADOS PERSONALIZADOS - Bradesco
$dadosboleto["conta_cedente"] = "627"; // ContaCedente do cliente, sem digito (Somente Números)
$dadosboleto["conta_cedente_dv"] = "0"; // Digito da ContaCedente do Cliente
$dadosboleto["carteira"] = "09";  // Código da Carteira: pode ser 06 ou 03
// SEUS DADOS
$dadosboleto["identificacao"] = "H B Santos Construtora e Incorporadora LTDA";
$dadosboleto["cpf_cnpj"] = "21.987.647/0001-29";
$dadosboleto["endereco"] = "Av. Brasil - Praia Grande";
$dadosboleto["cidade_uf"] = "Praia Grande / São Paulo";
$dadosboleto["cedente"] = "H B Santos Construtora e Incorporadora LTDA";
// echo('<script>window.print();</script>');
// NÃO ALTERAR!
include("/cboleto/include/funcoes_bradesco.php");
include("/cboleto/include/layout_bradesco.php");
?>