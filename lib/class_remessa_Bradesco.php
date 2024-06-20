<?php
//error_reporting(E_ALL);

include 'lib/file.php';

class remessa_Bradesco {

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
        <html><head><title>remessa - $this->identificacao_empresa </title></head><body $im>
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

        $codigo_banco                            = '237';
        $documento                               = minimo(unmask($data['documento']), 14, 1); # 14 numeros #*#
        $agencia = $this->agencia                = minimo($data['agencia'], 5, 1); # 5 digitos
        $conta_corrente = $this->conta_corrente  = minimo($data['conta_corrente'], 7, 1); # 7 digitos
        $conta_corrente_dv                       = modulo_11($data['conta_corrente']);
        $codigo_beneficiario                     = minimo($data['conta_corrente'], 6, true);
        $codigo_empresa                          = minimo($data['acessorio'], 20, true); #codigo fornecido pelo banco
        $nome_empresa                            = minimo($data['nome_empresa'], 30);
        $nome_banco                              = minimo('BRADESCO', 15);
        $data_arquivo                            = date('dmy'); # DDMMAA
        $numero_sequencia_remessa                = minimo($data['numero_sequencia_remessa'], 7, 1);  # Numero sequencial adotado e controlado pelo responsavel pela geração do aarquivo para ordenar a disposição dos arquivos encaminhados - Evoluir de 1 em 1

        // O numero de sequencia do registro será salvo no escopo da classe, para melhor gerência
        $this->numero_sequencia_registro         = minimo('1', 6, true);

        $header = '0'.'1'.'REMESSA'.'01'.'COBRANCA       '.$codigo_empresa.$nome_empresa.$codigo_banco.$nome_banco.$data_arquivo.vazios(8).'MX'.$numero_sequencia_remessa.vazios(277).$this->numero_sequencia_registro;

        $this->header = $header;
        return $this->header;
    }

    public function registro($data) {
        $agencia                   = minimo($data['agencia'], 5, 1); # 5 digitos
        $conta_corrente            = minimo($data['conta_corrente'], 7, 1); # 7 digitos
        $conta_corrente_dv         = modulo_11($data['conta_corrente']);
        $carteira = '009';
        $identificacao_empresa    = '0'.$carteira.$agencia.$conta_corrente.$conta_corrente_dv; #Zero(1) Carteira(3), Agência(5), CC(7) e CC_DV(1) 

        $valor_desconto_por_dia   = zeros(10);
        

        $aplica_multa             = '2'; # 2 - Aplica percentual de multa | 0 - Sem multa
        $numero_titulo            = minimo('1', 11, true);
        $data_vencimento_titulo   = date('dmy', strtotime($data['data_vencimento_titulo']));
        $valor_titulo             = minimo(unmask($data['valor_titulo']), 13, true);
        $especie_titulo           = '01'; # 99 = 'Outros' | 01 = 'Duplicata'
        $data_emissao_titulo      = date('dmy', strtotime($data['data_emissao_titulo']));
        $instrucoes_protesto      = '0000'; # Verificar página 20 - Campo 157 a 160

        $valor_por_dia_atrasado   = zeros(13);
        $data_limite_desconto     = zeros(6);
        $valor_desconto           = zeros(13);
        $valor_iof                = zeros(13);
        $valor_abatimento         = zeros(13);
        $tipo_documento_pagador   = '01'; # 1 = CPF | 2 = CNPJ | 98 = Não tem |99 = Outros
        $documento_pagador        = minimo(unmask($data['documento_pagador']), 14, true);
        $nome_pagador             = minimo($data['nome_pagador'], 40);
        $endereco_pagador         = minimo(($data['endereco_pagador']), 40);
        $mensagem                 = vazios(12);
        $cep_pagador              = minimo(unmask($data['cep_pagador']), 8, 1);

        if ( strpos($data['nome_empresa'], 'HB SANTOS') !== false ) {
            $percentual_multa       = '0200';
            $mensagem1 = minimo('Sr. Caixa, apos o vencimento cobrar 2% de multa', 80);
        } else {
            $percentual_multa       = '1000';
            $mensagem1 = minimo('Sr. Caixa, apos o vencimento cobrar 10% de multa', 80);
        }

        $mensagem2 = minimo('Nao receber apos 30 dias do vencimento.', 80);
        $decomposicao             = minimo('', 60);

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Nesse caso, o nosso numero está sendo salvo no banco, com o dv junto, portanto, não é preciso calculá-lo //
        $nosso_numero             = minimo($data['nosso_numero'], 12, 1);
        $dv_nosso_numero          = '';
        // $dv_nosso_numero          = digitoVerificadorBradesco_nossonumero('09'.$data['nosso_numero']);
        $nosso_numero_com_dv      = $nosso_numero.$dv_nosso_numero;
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $numero_documento            = minimo($data['numero_documento'], 10, true);
        
        $this->numero_sequencia_registro++;
        $numero_sequencia_registro_1 = minimo($this->numero_sequencia_registro, 6, true);

        $this->numero_sequencia_registro++;
        $numero_sequencia_registro_2 = minimo($this->numero_sequencia_registro, 6, true);

        $this->numero_sequencia_registro++;
        
        $controle_participante       = minimo('1', 25, true); # Campo de uso da Empresa, a informação que constar na Remessa será confirmada no Retorno,

        $registro_tipo_1 = '1'.zeros(19).$identificacao_empresa.$controle_participante.'000'.$aplica_multa.$percentual_multa.$nosso_numero_com_dv.$valor_desconto_por_dia.'2'.' '.vazios(10).' '.'2'.'  '.'01'.$numero_documento.$data_vencimento_titulo.$valor_titulo.'000'.'00000'.$especie_titulo.'N'.$data_emissao_titulo.$instrucoes_protesto.$valor_por_dia_atrasado.$data_limite_desconto.$valor_desconto.$valor_iof.$valor_abatimento.$tipo_documento_pagador.$documento_pagador.$nome_pagador.$endereco_pagador.$mensagem.$cep_pagador.$decomposicao.$numero_sequencia_registro_1;
        
        
        $mensagem3 = minimo('', 80);
        $mensagem4 = minimo('', 80);
        $data_limite_concessao_desconto_2 = date('dmy');
        $valor_desconto_2 = minimo('', 13, 1);
        $data_limite_concessao_desconto_3 = date('dmy');
        $valor_desconto_3 = minimo('', 13, 1);

        $registro_tipo_2 = '2'.$mensagem1.$mensagem2.$mensagem3.$mensagem4.$data_limite_concessao_desconto_2.$valor_desconto_2.$data_limite_concessao_desconto_3.$valor_desconto_3.vazios(7).$carteira.$this->agencia.$this->conta_corrente.modulo_11($this->conta_corrente).$nosso_numero_com_dv.$numero_sequencia_registro_2;

        $this->registros .= $registro_tipo_1."\r\n".$registro_tipo_2."\r\n";
        return $this->registros;
    }

    public function trailer($data) {

        $numero_sequencia_registro = minimo($this->numero_sequencia_registro, 6, true);
        $trailer = '9'.vazios(393).$numero_sequencia_registro;

        $txt = $this->header."\r\n".$this->registros.$trailer;	

        $filename = 'bra_'.date('dmYHi').'.txt';
        $path = 'remessa_arquivo/';
        $file = new file();
        $arquivo = $file->save($txt, $path.$filename);
        $file->prepare($path, $filename);
        exit;
    }
}

?>