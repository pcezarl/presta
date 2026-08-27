<?php

include_once 'lib/file.php';

/**
 * Remessa CNAB 240 do Banco ASA SCD (594), no layout Bradesco 240
 * (versao de arquivo 084, versao de lote 042).
 */
class remessa_ASA {

    const BANCO = '594';
    const LOTE  = '0001';

    const MENSAGEM_1 = 'Apos vencimento, cobrar multa de 2%';
    const MENSAGEM_2 = 'Nao receber apos 30 dias do vencimento.';

    public $header;
    public $registros;
    public $txt;

    private $sequencial_lote    = 0;
    private $quantidade_titulos = 0;
    private $valor_total        = 0;

    public function reset() {
        unset($this->header);
        unset($this->registros);
        unset($this->txt);
    }

    /**
     * Identificacao do Titulo — posicoes 38 a 57 do segmento P (20 posicoes).
     *
     * Layout confirmado pelo banco em 2026-08-04, apos analise do arquivo enviado:
     *   38-40  identificacao do produto = carteira (121)
     *   41-45  zeros
     *   46-56  nosso numero COM DV (10 digitos + 1 de DV)
     *   57     branco
     *
     * Historico: em 2026-07-27 o banco havia indicado "nosso numero com range + DV
     * das colunas 46 a 57", o que levou a colocar o DV isolado na 57, como faz o
     * layout Bradesco. A revisao acima corrige isso — o DV entra junto do numero,
     * dentro de 46-56, e a 57 fica em branco.
     */
    public function identificacao_titulo($carteira, $agencia, $nosso_numero) {
        $dv = asa_calc::dv_nosso_numero($agencia, $carteira, $nosso_numero);
        return str_pad($carteira, 3, '0', STR_PAD_LEFT)                  // 38-40
             . zeros(5)                                                  // 41-45
             . str_pad($nosso_numero, 10, '0', STR_PAD_LEFT) . $dv       // 46-56
             . ' ';                                                      // 57
    }

    public function header_arquivo($data) {
        return self::BANCO                                        // 1-3
             . '0000'                                             // 4-7
             . '0'                                                // 8
             . vazios(9)                                          // 9-17
             . '2'                                                // 18   CNPJ
             . minimo(unmask($data['documento']), 14, 1)          // 19-32
             . zeros(20)                                          // 33-52  convenio
             . minimo($data['agencia'], 5, 1)                     // 53-57
             . minimo($data['agencia_dv'], 1)                     // 58
             . minimo($data['conta_corrente'], 12, 1)             // 59-70
             . minimo($data['conta_dv'], 1)                       // 71
             . ' '                                                // 72
             . minimo($data['nome_empresa'], 30)                  // 73-102
             . minimo('BANCO ASA SCD', 30)                        // 103-132
             . vazios(10)                                         // 133-142
             . '1'                                                // 143  remessa
             . date('dmY')                                        // 144-151
             . date('His')                                        // 152-157
             . minimo($data['numero_sequencia_remessa'], 6, 1)    // 158-163
             . '084'                                              // 164-166
             . zeros(5)                                           // 167-171
             . vazios(20)                                         // 172-191
             . vazios(20)                                         // 192-211
             . vazios(29);                                        // 212-240
    }

    public function header_lote($data) {
        return self::BANCO                                        // 1-3
             . self::LOTE                                         // 4-7
             . '1'                                                // 8
             . 'R'                                                // 9
             . '01'                                               // 10-11
             . vazios(2)                                          // 12-13
             . '042'                                              // 14-16
             . ' '                                                // 17
             . '2'                                                // 18
             . minimo(unmask($data['documento']), 15, 1)          // 19-33
             . zeros(20)                                          // 34-53  convenio
             . minimo($data['agencia'], 5, 1)                     // 54-58
             . minimo($data['agencia_dv'], 1)                     // 59
             . minimo($data['conta_corrente'], 12, 1)             // 60-71
             . minimo($data['conta_dv'], 1)                       // 72
             . ' '                                                // 73
             . minimo($data['nome_empresa'], 30)                  // 74-103
             . minimo(self::MENSAGEM_1, 40)                       // 104-143
             . minimo(self::MENSAGEM_2, 40)                       // 144-183
             . minimo($data['numero_sequencia_remessa'], 8, 1)    // 184-191
             . date('dmY')                                        // 192-199
             . zeros(8)                                           // 200-207
             . vazios(33);                                        // 208-240
    }

    private function prefixo_detalhe($segmento) {
        return self::BANCO                                        // 1-3
             . self::LOTE                                         // 4-7
             . '3'                                                // 8
             . minimo($this->sequencial_lote, 5, 1)               // 9-13
             . $segmento                                          // 14
             . ' '                                                // 15
             . '01';                                              // 16-17
    }

    public function segmento_p($data) {
        $vencimento = date('dmY', strtotime($data['data_vencimento_titulo']));
        $juros_em   = date('dmY', strtotime($data['data_vencimento_titulo'] . ' +1 day'));

        return $this->prefixo_detalhe('P')                        // 1-17
             . minimo($data['agencia'], 5, 1)                     // 18-22
             . minimo($data['agencia_dv'], 1)                     // 23
             . minimo($data['conta_corrente'], 12, 1)             // 24-35
             . minimo($data['conta_dv'], 1)                       // 36
             . ' '                                                // 37
             . $this->identificacao_titulo($data['carteira'], $data['agencia'], $data['nosso_numero']) // 38-57
             . '2'                                                // 58  cobranca vinculada
             . '1'                                                // 59  com cadastramento
             . '2'                                                // 60  tipo de documento
             . '2'                                                // 61  cliente emite
             . '2'                                                // 62  cliente distribui
             . minimo($data['numero_documento'], 15)              // 63-77
             . $vencimento                                        // 78-85
             . asa_calc::valor_centavos($data['valor_titulo'], 15) // 86-100
             . zeros(5)                                           // 101-105 agencia cobradora
             . ' '                                                // 106
             . '02'                                               // 107-108 DM
             . 'N'                                                // 109 aceite
             . date('dmY', strtotime($data['data_emissao_titulo'])) // 110-117
             . '2'                                                // 118 juros: taxa mensal
             . $juros_em                                          // 119-126
             . minimo('100', 15, 1)                               // 127-141 1,00%
             . '0'                                                // 142 sem desconto
             . zeros(8)                                           // 143-150
             . zeros(15)                                          // 151-165
             . zeros(15)                                          // 166-180 IOF
             . zeros(15)                                          // 181-195 abatimento
             . minimo($data['numero_documento'], 25)              // 196-220
             . '3'                                                // 221 nao protestar
             . '00'                                               // 222-223
             . '1'                                                // 224 baixar/devolver
             . '030'                                              // 225-227
             . '09'                                               // 228-229 real
             . zeros(10)                                          // 230-239 numero do contrato
             . ' ';                                               // 240
    }

    public function segmento_q($data) {
        $cep  = minimo(unmask($data['cep_pagador']), 8, 1);
        $tipo = strlen(unmask($data['documento_pagador'])) > 11 ? '2' : '1';

        return $this->prefixo_detalhe('Q')                        // 1-17
             . $tipo                                              // 18
             . minimo(unmask($data['documento_pagador']), 15, 1)  // 19-33
             . minimo($data['nome_pagador'], 40)                  // 34-73
             . minimo($data['endereco_pagador'], 40)              // 74-113
             . minimo($data['bairro_pagador'], 15)                // 114-128
             . substr($cep, 0, 5)                                 // 129-133
             . substr($cep, 5, 3)                                 // 134-136
             . minimo($data['cidade_pagador'], 15)                // 137-151
             . minimo($data['estado_pagador'], 2)                 // 152-153
             . '0'                                                // 154 sem beneficiario final
             . zeros(15)                                          // 155-169
             . vazios(40)                                         // 170-209
             . zeros(3)                                           // 210-212 desvio ASA
             . vazios(20)                                         // 213-232
             . vazios(8);                                         // 233-240
    }

    public function segmento_r($data) {
        $multa_em = date('dmY', strtotime($data['data_vencimento_titulo'] . ' +1 day'));

        return $this->prefixo_detalhe('R')                        // 1-17
             . '0'                                                // 18 sem desconto 2
             . zeros(8)                                           // 19-26
             . zeros(15)                                          // 27-41
             . '0'                                                // 42 sem desconto 3
             . zeros(8)                                           // 43-50
             . zeros(15)                                          // 51-65
             . '2'                                                // 66 multa percentual
             . $multa_em                                          // 67-74
             . minimo('200', 15, 1)                               // 75-89 2,00%
             . vazios(10)                                         // 90-99
             . vazios(40)                                         // 100-139 mensagem 3
             . vazios(40)                                         // 140-179 mensagem 4
             . vazios(20)                                         // 180-199
             . zeros(8)                                           // 200-207
             . zeros(3)                                           // 208-210
             . zeros(5)                                           // 211-215
             . ' '                                                // 216
             . zeros(12)                                          // 217-228
             . ' '                                                // 229
             . ' '                                                // 230
             . '0'                                                // 231
             . vazios(9);                                         // 232-240
    }

    public function trailer_lote() {
        // header de lote + detalhes + o proprio trailer
        $quantidade_registros = $this->sequencial_lote + 2;

        return self::BANCO                                        // 1-3
             . self::LOTE                                         // 4-7
             . '5'                                                // 8
             . vazios(9)                                          // 9-17
             . minimo($quantidade_registros, 6, 1)                // 18-23
             . zeros(6)                                           // 24-29  simples
             . zeros(17)                                          // 30-46
             . minimo($this->quantidade_titulos, 6, 1)            // 47-52  vinculada
             . asa_calc::valor_centavos($this->valor_total, 17)   // 53-69
             . zeros(6)                                           // 70-75  caucionada
             . zeros(17)                                          // 76-92
             . zeros(6)                                           // 93-98  descontada
             . zeros(17)                                          // 99-115
             . zeros(8)                                           // 116-123
             . vazios(117);                                       // 124-240
    }

    public function trailer_arquivo() {
        // header de arquivo + header de lote + detalhes + trailer de lote + este
        $total_registros = $this->sequencial_lote + 4;

        return self::BANCO                                        // 1-3
             . '9999'                                             // 4-7
             . '9'                                                // 8
             . vazios(9)                                          // 9-17
             . minimo('1', 6, 1)                                  // 18-23 um lote
             . minimo($total_registros, 6, 1)                     // 24-29
             . zeros(6)                                           // 30-35
             . vazios(205);                                       // 36-240
    }

    public function header($data) {
        $this->registros = '';
        $this->header    = $this->header_arquivo($data) . "\r\n" . $this->header_lote($data);
        return $this->header;
    }

    public function registro($data) {
        $this->sequencial_lote++;
        $p = $this->segmento_p($data);
        $this->sequencial_lote++;
        $q = $this->segmento_q($data);
        $this->sequencial_lote++;
        $r = $this->segmento_r($data);

        $this->quantidade_titulos++;
        $this->valor_total += (float) $data['valor_titulo'];

        $this->registros .= $p . "\r\n" . $q . "\r\n" . $r . "\r\n";
        return $this->registros;
    }

    public function trailer($data) {
        $txt = $this->header . "\r\n" . $this->registros
             . $this->trailer_lote() . "\r\n" . $this->trailer_arquivo();

        $filename = 'asa_' . date('dmYHi') . '.rem';
        $path     = 'remessa_arquivo/';
        $file     = new file();
        $file->save($txt, $path . $filename);
        $file->prepare($path, $filename);
        exit;
    }
}
