    <?php

    include 'lib/file.php';

    class remessa_SICOOB {

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

            $this->valor_total          = 0;
            $this->quantidade_total_titulos   = 0;
            $this->codigo_banco         = '756';
            $this->codigo_lote          = '0000';
            $tipo_registro              = '0'; # 0: Header Arquivo - 1: Header Lote -
            $tipo_pessoa                = '2'; # 1: CPF - 2: CNPJ #*#
            $documento                  = minimo(unmask($data['documento']), 14, 1); # Inscricao Estadual (14)
            $numero_inscricao           = minimo(unmask($documento), 14, 1); 
            $codigo_cooperativa         = minimo($data['agencia'], 6, 1); #Codigo Cooperativa (5)
            $this->codigo_beneficiario  = minimo($data['conta_corrente'], 13, true);
            $nome_empresa               = minimo($data["nome_empresa"], 30);
            $nome_banco                 = minimo($_SESSION['banco'], 30);
            $data_arquivo               = date('dmY');
            $hora_arquivo               = date('His'); # Detalhe...o verificar o GMT #
            $versao_layout              = '081';
            $densidade                  = '00000';
            $numero_sequencia_arquivo   = minimo($data['numero_sequencia_remessa'], 6, 1);  # Numero sequencial adotado e controlado pelo responsavel pela geração do aarquivo para ordenar a disposição dos arquivos encaminhados - Evoluir de 1 em 1

            $this->numero_sequencia_registro = $data['numero_sequencia_registro'];

            $header_arquivo = 
                $this->codigo_banco.$this->codigo_lote.$tipo_registro.vazios(9)
                .$tipo_pessoa.minimo(unmask($documento),14, true).vazios(20)
                .$codigo_cooperativa.$this->codigo_beneficiario.'0'.$nome_empresa.$nome_banco.vazios(10)
                .'1'.$data_arquivo.$hora_arquivo.$numero_sequencia_arquivo.$versao_layout.$densidade.vazios(69);

            
            $header_lote =
                $this->codigo_banco.$this->codigo_lote.'1'.'R'.'01'.vazios(2).'040'.' '
                .'2'.minimo($numero_inscricao, 15, true).vazios(20).$codigo_cooperativa.$this->codigo_beneficiario.' '
                .$nome_empresa.$mensagem1.$mensagem2.minimo($numero_sequencia_arquivo, 8, 1).date('dmY').zeros(8).vazios(33);

            $this->quantidade_registros = 1;

            $this->header = $header_arquivo."\r\n".$header_lote;
            return $this->header;
        
    }
        public function registro($data) {

            $this->codigo_lote_servico  = minimo('000', 4, 1); #A cada novo lote, pegar esse numero + 1   
            $modalidade                 = '01'; # Com registro - boleto emitido pelo beneficiario
            $carteira                   = '1';
            $data_vencimento            = minimo(unmask(date('dmY', strtotime($data['data_vencimento_titulo']))), 8, 1);
            $codigo_cooperativa         = minimo($data['agencia'], 6, 1);
            $identificacao_titulo       = minimo($data['numero_documento'], 10, 1);
            $valor_titulo               = minimo(unmask($data['valor_titulo']), 15, 1);
            $aceite                     = 'A'; #A: possui aceite - N: Não possui aceite
            $codigo_juros               = '2'; // 0: Isento | 1: Valor por Dia | 2: Taxa Mensal
            $data_juros                 = minimo(unmask(date('dmY', strtotime($data['data_vencimento_titulo']. '+1 day'))), 8, 1);
            $data_emissao_titulo        = date('dmY', strtotime($data['data_emissao_titulo']));
            $numero_sequencia_registro  = minimo('1', 5, 1);
            $emissor_boleto             = '2'; // 1: Sicoob Emite | 2: Beneficiário Emite
            $distribuidor_boleto        = '2'; // 1: Sicoob Emite | 2: Beneficiário Emite
            $juros_mora                 = minimo('100', 15, 1);
            $codigo_desconto            = '0'; // 0: Não Conceder desconto | 1: Valor Fixo Até a Data Informada | 2: Percentual Até a Data Informada
            $data_desconto              = zeros(8); 
            $valor_desconto             = zeros(15); #Valor ou % do desconto á ser concedido - C023
            $valor_iof                  = zeros(15);
            $valor_abatimento           = zeros(15);
            $codigo_protesto            = '3'; // 1' Protestar dias corridos | 3: Não Protestar/Não Negativar | 8: Negativação sem Protesto | 9: Cancelamento Protesto/Negativação Automática
            $dias_para_protestar        = '00'; #Numero de dias após a dt de vcto p/ iniciar protesto | 00: Não Protestar
            $codigo_baixa_devolucao     = '0';
            $dias_para_baixa            = vazios(3);
            $codigo_moeda               = '09'; # 09: Real

            $tipo_documento_pagador     = strlen($data['documento_pagador']) > 13 ? '2': '1'; #1: CPF - 2: CNPJ
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
            $nosso_numero = $identificacao_titulo.minimo($data['parcela'], 2, 1).$modalidade.'1'.vazios(5);

            $registro_p =
                $this->codigo_banco.$this->codigo_lote_servico.'3'.minimo($this->quantidade_registros, 5, 1).'P'.' '.'01'.$codigo_cooperativa.$this->codigo_beneficiario.' '
                .$nosso_numero.$carteira.'0'.' '.$emissor_boleto.$distribuidor_boleto.minimo($data['numero_documento'], 15, 1).$data_vencimento.$valor_titulo.zeros(5).' '.'02'.$aceite
                .$data_emissao_titulo.$codigo_juros.$data_juros.$juros_mora.$codigo_desconto.$data_desconto
                .$valor_desconto.$valor_iof.$valor_abatimento
                .minimo($identificacao_titulo, 25, true).$codigo_protesto.$dias_para_protestar
                .$codigo_baixa_devolucao.$dias_para_baixa.$codigo_moeda.zeros(10).' ';
            $this->quantidade_registros++;
            
            $registro_q =
                $this->codigo_banco.$this->codigo_lote_servico.'3'.minimo($this->quantidade_registros, 5, 1).'Q'.' '.'01'
                .$tipo_documento_pagador.$documento_pagador.$nome_pagador.$endereco_pagador.$bairro_pagador.$cep_pagador.$cidade_pagador.$uf_pagador
                .'0'.zeros(15).vazios(40).'000'.vazios(20).vazios(8);
            $this->quantidade_registros++;

            // pre(strlen($registro_q));
            $registro_r = 
                $this->codigo_banco.$this->codigo_lote_servico.'3'.minimo($this->quantidade_registros, 5, 1).'R'.' '.'01'.'0'
                .zeros(8).vazios(15).'0'.zeros(8).vazios(15).$codigo_multa.$data_multa.$percentual_multa.vazios(10).$mensagem3.$mensagem4
                .vazios(20).$data_vencimento.zeros(8).' '.zeros(12).vazios(2).'0'.vazios(9);
            $this->quantidade_registros++;

            $registro_s = 
                $this->codigo_banco.$this->codigo_lote_servico.'3'.minimo($this->quantidade_registros, 5, 1).'S'.' '.'01'.'3'.vazios(222);

                $this->valor_total += $data['valor_titulo'];
                $this->quantidade_total_titulos++;
            

            $this->registros .= $registro_p."\r\n".$registro_q."\r\n".$registro_r."\r\n".$registro_s."\r\n";
            return $this->registros;
        }

        public function trailer($data) {

            $filler = zeros(69);
            $this->quantidade_registros++;
            $quantidade_registros = minimo($this->quantidade_registros, 6, 1);

            $registro_5 = $this->codigo_banco.$this->codigo_lote_servico.'5'.vazios(9).$quantidade_registros.minimo($this->quantidade_total_titulos, 6,1).minimo($this->valor_total, 17, 1).$filler.vazios(125);
            
            $quantidade_total_registros = minimo($this->quantidade_registros +2, 6, 1);
            $registro_9 = $this->codigo_banco.'9999'.'9'.vazios(9).minimo('1', 6, true).$quantidade_total_registros.vazios(6).vazios(205);
            
            $txt = $this->header."\r\n".$this->registros.$registro_5."\r\n".$registro_9;

            $filename = 'sicoob_'.date('dmYHi').'.rem';
            $path = 'remessa_arquivo/';
            $file = new file();
            $file->save($txt, $path.$filename);
            $file->prepare($path, $filename);
            exit;
        }
    }

    ?>