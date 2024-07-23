<?php
// error_reporting(E_ALL);

include 'lib/file.php';

class remessa_CEF {

    public $header;
    public $registros;
    public $trailer;
    public $txt;

    public function getHeader() {
        return $this->header();
    }
    public function getRegistros() {
        return $this->registros();
    }
    public function getTrailer() {
        return $this->trailer();
    }

    public function init(){

        $im=($this->imprimir)?"onload='print()'":"";

        $this->layout="
        <html><head><title>Remessa - $this->identificacao_empresa </title></head><body $im>
        ".$this->layout;

    }

    public function set($t,$v){
        $this->layout=preg_replace("(\{\{$t\}\})","$v",$this->layout);
    }

    public function reset(){
        unset($this->header);
        unset($this->registros);
        unset($this->registro);
        unset($this->trailer);
        unset($this->txt);
    }

    public function val($t,$v){
        $this->dadosboleto[$t]=$v;
    }

    public function header($data) {

        $mensagem1 = minimo('Apos vencimento, cobrar multa de 2%', 40);
        $mensagem2 = minimo('', 40);

        $this->codigo_banco         = '104';
        $this->codigo_lote          = '0001';
        $tipo_registro              = '0'; # 0: Header Arquivo - 1: Header Lote -
        $tipo_pessoa                = '2'; # 1: CPF - 2: CNPJ #*#
        $documento                  = minimo(unmask($data['documento']), 14, 1); # 14 numeros #*#
        $numero_inscricao           = minimo(unmask($documento), 15, 1); 
        $agencia                    = minimo($data['agencia'], 5, 1); # 5 digitos - sendo um 0 á esquerda
        $dv_agencia                 = modulo_11($data['agencia']);
        $this->codigo_beneficiario  = minimo('729361', 6, true);
        $nome_empresa               = minimo('HB SANTOS CONSTRUTORA E INCORPORADORA LTDA', 30);
        $nome_banco                 = minimo('CAIXA ECONOMICA FEDERAL', 30);
        $codigo_arquivo             = '1'; # 1: Remessa - 2: Retorno
        $data_arquivo               = date('dmY');
        $hora_arquivo               = date('His'); # Detalhe...o verificar o GMT #
        $versao_layout              = '050';
        $situacao_arquivo           = minimo('REMESSA-PRODUCAO', 20); #REMESSA TESTE OU REMESSA PRODUCAO
        $numero_sequencia_arquivo   = minimo($data['numero_sequencia_remessa'], 6, 1);  # Numero sequencial adotado e controlado pelo responsavel pela geração do aarquivo para ordenar a disposição dos arquivos encaminhados - Evoluir de 1 em 1

        $this->numero_sequencia_registro = $data['numero_sequencia_registro'];

        $header_arquivo = $this->codigo_banco.'0000'.'0'.vazios(9).$tipo_pessoa.minimo(unmask($documento),14, true).zeros(20).$agencia.$dv_agencia.$this->codigo_beneficiario.zeros(8).$nome_empresa.$nome_banco.vazios(10).'1'.$data_arquivo.$hora_arquivo.$numero_sequencia_arquivo.$versao_layout.'00000'.vazios(20).$situacao_arquivo.vazios(4).vazios(25);
        
        $header_lote = '104'.$this->codigo_lote.'1'.'R'.'01'.'00'.'030'.' '.'2'.$numero_inscricao.$this->codigo_beneficiario.zeros(14).$agencia.$dv_agencia.$this->codigo_beneficiario.zeros(7).'0'.$nome_empresa.$mensagem1.$mensagem2.'00'.$numero_sequencia_arquivo.date('dmY').zeros(8).vazios(33);
        $this->quantidade_registros = 1;

        $this->header = $header_arquivo."\r\n".$header_lote;
        return $this->header;
    
}
    public function registro($data) {

        $this->codigo_lote_servico  = minimo('000', 4, true); #A cada novo lote, pegar esse numero + 1   
        $modalidade                 = '14'; # Com registro - boleto emitido pelo beneficiario
        $data_vencimento            = minimo(unmask(date('dmY', strtotime($data['data_vencimento_titulo']))), 8, 1);
        $agencia                    = minimo($data['agencia'], 5, 1);
        $dv_agencia                 = modulo_11($data['agencia']);
        $identificacao_titulo       = minimo($data['numero_documento'], 15, 1);
        $numero_controle_titulo     = minimo($data['numero_documento'], 11, 1);
        $valor_titulo               = minimo(unmask($data['valor_titulo']), 15, true);
        $aceite                     = 'A'; #A: possui aceite - N: Não possui aceite
        $codigo_juros               = '2'; #1: valor por dia  - 2: taxa mensal - 3: isento
        $data_juros                 = minimo(unmask(date('dmY', strtotime($data['data_vencimento_titulo']. '+1 day'))), 8, 1);
        $data_emissao_titulo        = date('dmY', strtotime($data['data_emissao_titulo']));
        $numero_sequencia_registro  = minimo('2', 5, 1);
        // $numero_sequencia_registro  = minimo($data['numero_sequencia_remessa'], 5, 1);
        $juros_mora                 = minimo('100', 15, 1);
        $codigo_desconto            = '0'; # Referencia -> C021
        $data_desconto              = zeros(8); # Referencia -> C022 
        $valor_desconto             = zeros(15); #Valor ou % do desconto á ser concedido - C023
        $valor_iof                  = zeros(15);
        $valor_abatimento           = zeros(15);
        $codigo_protesto            = '3'; #1: Protestar - 2: Não Protestar - C026
        $dias_para_protestar        = '05'; #Numero de dias após a dt de vcto p/ iniciar protesto
        $codigo_baixa_devolucao     = '1';
        $dias_para_baixa            = '030'; # C029
        $codigo_moeda               = '09'; # 09: Real

        $tipo_documento_pagador     = '1'; #1: CPF - 2: CNPJ
        $documento_pagador          = minimo(unmask($data['documento_pagador']), 15, true);
        $nome_pagador               = minimo($data['nome_pagador'], 40);
        $endereco_pagador           = minimo($data['endereco_pagador'], 40);
        $bairro_pagador             = minimo($data['bairro_pagador'], 15);
        $cep_pagador                = minimo(unmask($data['cep_pagador']), 8, 1);
        $cidade_pagador             = minimo($data['cidade_pagador'], 15);
        $uf_pagador                 = minimo($data['estado_pagador'], 2);

        $codigo_multa               = '2'; #2 = percentual | 1 = valor fixo'
        $data_multa                 = minimo(unmask(date('dmY', strtotime($data['data_vencimento_titulo']))), 8, 1);
        $percentual_multa           = minimo('200', 15, 1);
        $mensagem3                  = vazios(40);
        $mensagem4                  = vazios(40);

        $registro_p = $this->codigo_banco.$this->codigo_lote_servico.'3'.$numero_sequencia_registro.'P'.' '.'01'.$agencia.$dv_agencia.$this->codigo_beneficiario.zeros(8).zeros(3).$modalidade.$identificacao_titulo.'1'.'1'.'2'.'2'.'0'.$numero_controle_titulo.vazios(4).$data_vencimento.$valor_titulo.zeros(5).'0'.'02'.$aceite.$data_emissao_titulo.$codigo_juros.$data_juros.$juros_mora.$codigo_desconto.$data_desconto.$valor_desconto.$valor_iof.$valor_abatimento.minimo($identificacao_titulo, 25, true).$codigo_protesto.$dias_para_protestar.$codigo_baixa_devolucao.$dias_para_baixa.$codigo_moeda.zeros(10).' ';
$this->quantidade_registros++;

        $registro_q = $this->codigo_banco.$this->codigo_lote_servico.'3'.$numero_sequencia_registro.'Q'.' '.'01'.$tipo_documento_pagador.$documento_pagador.$nome_pagador.$endereco_pagador.$bairro_pagador.$cep_pagador.$cidade_pagador.$uf_pagador.'0'.zeros(15).vazios(40).'000'.vazios(20).vazios(8);
$this->quantidade_registros++;

        $registro_r = $this->codigo_banco.$this->codigo_lote_servico.'3'.$numero_sequencia_registro.'R'.' '.'01'.'0'.zeros(8).vazios(15).'0'.zeros(8).vazios(15).$codigo_multa.$data_multa.$percentual_multa.vazios(10).$mensagem3.$mensagem4.vazios(50).vazios(11);
$this->quantidade_registros++;

        $this->registros .= $registro_p."\r\n".$registro_q."\r\n".$registro_r."\r\n";
        return $this->registros;
    }

    public function trailer($data) {

        $filler = zeros(69);
        $this->quantidade_registros++;
        $quantidade_registros = minimo($this->quantidade_registros, 6, 1);

        $registro_5 = $this->codigo_banco.$this->codigo_lote_servico.'5'.vazios(9).$quantidade_registros.$filler.vazios(31).vazios(117);

        $quantidade_total_registros = minimo($this->quantidade_registros +2, 6, 1);
        $registro_9 = $this->codigo_banco.'9999'.'9'.vazios(9).minimo('1', 6, true).$quantidade_total_registros.vazios(6).vazios(205);

        $txt = $this->header."\r\n".$this->registros.$registro_5."\r\n".$registro_9;

        $filename = 'cef_'.date('dmYHi').'.rem';
        $path = 'remessa_arquivo/';
        $file = new file();
        $arquivo = $file->save($txt, $path.$filename);
        $file->prepare($path, $filename);
        exit;
    }
}

?>