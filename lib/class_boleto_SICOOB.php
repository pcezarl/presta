<?php

class boleto_SICOOB
{
  // configuracao das instrucoes do boleto
  const inst1 = "Sr. Caixa, cobrar multa de 2%";
  const inst2 = "Não receber após 30 dias do vencimento.";
  const inst3 = "";
  const inst4 = "";

  private $carteira = "1";  
  private $modalidade = "01";
  private $codigobanco = "756";
  private $nummoeda = "9";


  private function digitoVerificador_cedente($numero)
  {
    $resto2 = $this->modulo_11($numero, 9, 1);
    $digito = 11 - $resto2;
    if ($digito == 10 || $digito == 11)
      $digito = 0;
    $dv = $digito;
    return $dv;
  }

  private function digitoVerificador_barra($numero)
  {
    $resto2 = $this->modulo_11($numero, 9, 1);
    if ($resto2 == 0 || $resto2 == 1 || $resto2 == 10) {
      $dv = 1;
    } else {
      $dv = 11 - $resto2;
    }
    return $dv;
  }

  private function formata_numero($numero, $loop, $insert, $tipo = "geral")
  {
    if ($tipo == "geral") {
      $numero = str_replace(",", "", $numero);
      while (strlen($numero) < $loop) {
        $numero = $insert . $numero;
      }
      return $numero;
    }


    if ($tipo == "valor") {
      $numero = str_replace(",", "", $numero);
      //$numero = str_replace(".","",$numero);

      while (strlen($numero) < $loop) {
        $numero = $insert . $numero;
      }
      return $numero;
    }


    if ($tipo == "convenio") {
      while (strlen($numero) < $loop) {
        $numero = $numero . $insert;
      }

      return $numero;
    }


    if ($tipo == "digitavel") {

      if ((!is_int((integer) $this->dadosboleto['valor_boleto']))) {
        $numero = str_replace(".", "", $numero);
        $cents = false;
      } else {
        $cents = true;
      }

      $numero = str_replace(",", "", $numero);
      //$numero = str_replace(".","",$numero);

      $cents = true;
      if ($cents == true) {
        $numero *= 100;
      }

      while (strlen($numero) < $loop) {
        $numero = $insert . $numero;
      }



      return $numero;
    }
  }


  private function dvNossoNumero($nnum)
  {
    $count = 0;
    $calculoDv = 0;
    for ($i = 0; $i <= strlen($nnum); $i++) {
      $count++;
      if ($count == 1) {
        $constante = 3;
      }
      if ($count == 2) {
        $constante = 1;
      }
      if ($count == 3) {
        $constante = 9;
      }
      if ($count == 4) {
        $constante = 7;
        $count = 0;
      }
      $calculoDv = $calculoDv + (substr($nnum, $i, 1) * $constante);
    }

    $resto = $calculoDv % 11;
    if ($resto == 0 || $resto == 1) {
      $dv = 0;
    } else {
      $dv = 11 - $resto;
    }
    return $dv;
  }


  private function geraNossoNumero($ndoc, $cedente, $venc, $tipoid)
  {
    $ndoc = $ndoc . $this->modulo_11_invertido($ndoc) . $tipoid;
    $venc = substr($venc, 0, 2) . substr($venc, 3, 2) . substr($venc, 8, 2);
    $res = $ndoc + $cedente + $venc;
    return $ndoc . $this->modulo_11_invertido($res);
  }

  private function dataJuliano($data)
  {
    $dia = (int) substr($data, 0, 2);
    $mes = (int) substr($data, 3, 2);
    $ano = (int) substr($data, 6, 4);
    $dataf = strtotime("$ano/$mes/$dia");
    $datai = strtotime(($ano - 1) . '/12/31');
    $dias = (int) (($dataf - $datai) / (60 * 60 * 24));
    return str_pad($dias, 3, '0', STR_PAD_LEFT) . substr($data, 9, 4);
  }

  private function modulo_10($num)
  {
    $numtotal10 = 0;
    $fator = 2;

    // Separacao dos numeros
    for ($i = strlen($num); $i > 0; $i--) {
      // pega cada numero isoladamente
      $numeros[$i] = substr($num, $i - 1, 1);
      // Efetua multiplicacao do numero pelo (falor 10)
      // 2002-07-07 01:33:34 Macete para adequar ao Mod10 do ItaÃº
      $temp = $numeros[$i] * $fator;
      $temp0 = 0;
      foreach (preg_split('//', $temp, -1, PREG_SPLIT_NO_EMPTY) as $k => $v) {
        $temp0 += $v;
      }
      $parcial10[$i] = $temp0; //$numeros[$i] * $fator;
      // monta sequencia para soma dos digitos no (modulo 10)
      $numtotal10 += $parcial10[$i];
      if ($fator == 2) {
        $fator = 1;
      } else {
        $fator = 2; // intercala fator de multiplicacao (modulo 10)
      }
    }

    $resto = $numtotal10 % 10;
    $digito = 10 - $resto;
    if ($resto == 0) {
      $digito = 0;
    }

    return $digito;

  }

  private function modulo_11($num, $base = 9, $r = 0)
  {

    $soma = 0;
    $fator = 2;

    /* Separacao dos numeros */
    for ($i = strlen($num); $i > 0; $i--) {
      // pega cada numero isoladamente
      $numeros[$i] = substr($num, $i - 1, 1);
      // Efetua multiplicacao do numero pelo falor
      $parcial[$i] = $numeros[$i] * $fator;
      // Soma dos digitos
      $soma += $parcial[$i];
      if ($fator == $base) {
        // restaura fator de multiplicacao para 2
        $fator = 1;
      }
      $fator++;
    }

    /* Calculo do modulo 11 */
    if ($r == 0) {
      $soma *= 10;
      $digito = $soma % 11;
      if ($digito == 10) {
        $digito = 0;
      }
      return $digito;
    } elseif ($r == 1) {
      $resto = $soma % 11;
      return $resto;
    }
  } //fim mod11

  private function modulo_11_invertido($num, $ftInicio = 9)
  { // Calculo de Modulo 11 "Invertido" (com pesos de 9 a 2 e não de 2 a 9)
    $ftini = 2;
    $ftfim = 9;
    $fator = $ftInicio;
    $soma = 0;

    for ($i = strlen($num); $i > 0; $i--) {
      $soma += substr($num, $i - 1, 1) * $fator;
      if (--$fator < $ftini)
        $fator = $ftfim;
    }

    $digito = $soma % 11;
    if ($digito > 9 || $digito === 0 ) {
      $digito = 1;
    }
    return $digito;
  }

  private function _fbarcode($valor)
  {
    $fino = 1;
    $largo = 3;
    $altura = 50;

    $barcodes[0] = "00110";
    $barcodes[1] = "10001";
    $barcodes[2] = "01001";
    $barcodes[3] = "11000";
    $barcodes[4] = "00101";
    $barcodes[5] = "10100";
    $barcodes[6] = "01100";
    $barcodes[7] = "00011";
    $barcodes[8] = "10010";
    $barcodes[9] = "01010";
    for ($f1 = 9; $f1 >= 0; $f1--) {
      for ($f2 = 9; $f2 >= 0; $f2--) {
        $f = ($f1 * 10) + $f2;
        $texto = "";
        for ($i = 1; $i < 6; $i++) {
          $texto .= substr($barcodes[$f1], ($i - 1), 1) . substr($barcodes[$f2], ($i - 1), 1);
        }
        $barcodes[$f] = $texto;
      }
    }


    //Desenho da barra
    //Guarda inicial
?><img src="cboleto/imagens/p.png" width=<?php echo $fino ?> height=<?php echo $altura ?> border=0><img
      src="cboleto/imagens/b.png" width=<?php echo $fino ?> height=<?php echo $altura ?> border=0><img
      src="cboleto/imagens/p.png" width=<?php echo $fino ?> height=<?php echo $altura ?> border=0><img
      src="cboleto/imagens/b.png" width=<?php echo $fino ?> height=<?php echo $altura ?> border=0><img <?php
            $texto = $valor;
            if ((strlen($texto) % 2) <> 0) {
              $texto = "0" . $texto;
            }

            // Draw dos dados
            while (strlen($texto) > 0) {
              $i = round($this->esquerda($texto, 2));
              $texto = $this->direita($texto, strlen($texto) - 2);
              $f = $barcodes[$i];
              for ($i = 1; $i < 11; $i += 2) {
                if (substr($f, ($i - 1), 1) == "0") {
                  $f1 = $fino;
                } else {
                  $f1 = $largo;
                }
                ?>
          src="cboleto/imagens/p.png" width=<?php echo $f1 ?> height=<?php echo $altura ?> border=0><img <?php
                if (substr($f, $i, 1) == "0") {
                  $f2 = $fino;
                } else {
                  $f2 = $largo;
                }
                ?> src="cboleto/imagens/b.png" width=<?php echo $f2 ?>
          height=<?php echo $altura ?> border=0><img <?php
              }
            }

            // Draw guarda final
            ?> src="cboleto/imagens/p.png" width=<?php echo $largo ?> height=<?php echo $altura ?> border=0><img src="cboleto/imagens/b.png" width=<?php echo $fino ?>
      height=<?php echo $altura ?> border=0><img src="cboleto/imagens/p.png" width=<?php echo 1 ?> height=<?php echo $altura ?>
      border=0>
<?php
} //Fim da função

  private function fbarcode($n)
  {
    ob_start();
    $this->_fbarcode($n);
    return ob_get_clean();
  }


  private function esquerda($entra, $comp)
  {
    return substr($entra, 0, $comp);
  }

  private function direita($entra, $comp)
  {
    return substr($entra, strlen($entra) - $comp, $comp);
  }
  

  function monta_linha_digitavel()
  {
    // | Campo 1		 | Campo 2	    | Campo 3      | Campo 4 | Campo 5
    // | AAABC.DDDDE | FFGGG.GGGGHI | HHHHH.HHJJJK | L       | MMMMNNNNNNNNNN
					
    // A=	Código do Sicoob na câmara de compensação - "756"
    // B=	Código da moeda - "9"
    // C=	Código da carteira - verificar na planilha "Capa" deste arquivo
    // D=	Código da agência/cooperativa - verificar na planilha "Capa" deste arquivo
    // E=	Dígito verificador do Campo 1 - vide demonstrativo de cálculo a seguir
    // F=	Código da modalidade - verificar na planilha "Capa" deste arquivo
    // G=	Código do beneficiário/cliente - verificar na planilha "Capa" deste arquivo
    // H=	Nosso número do boleto
    // I=	Dígito verificador do Campo 2 - vide demonstrativo de cálculo a seguir
    // J=	Número da parcela a que o boleto se refere - "001" se parcela única
    // K=	Dígito verificador do Campo 3 - vide demonstrativo de cálculo a seguir
    // L=	Dígito verificador do Código de Barras - vide demonstrativo de cálculo a seguir
    // M=	Fator de vencimento - vide demonstrativo de cálculo a seguir
    // N=	Valor do boleto - Em casos de cobrança com valor em aberto (o valor a ser pago é preenchido pelo próprio pagador) ou cobrança em moeda variável, deve ser preenchido com zeros
    $a = $this->formata_numero($this->codigobanco,3,0);   // AAA
    $b = $this->formata_numero($this->nummoeda, 1, 0);    // B
    $c = $this->formata_numero($this->carteira, 1, 0);   // C
    $d = $this->formata_numero($this->codigo_cooperativa, 4, 0);   // DDDD
    $e = $this->modulo_10($a.$b.$c.$d);   // E
    $f = $this->formata_numero($this->modalidade, 2, 0);   // FF
    $g = $this->formata_numero($this->codigo_cliente, 7, 0);   // GGGGGGG
    $h1 = substr($this->nosso_numero_com_dv, 0, 1);  //  H 
    $i = $this->modulo_10($f.$g.$h1);   // I 
    $h2 = substr($this->nosso_numero_com_dv, 1, 7);  //  HHHHHHH 
    $j = $this->formata_numero($this->parcela, 3, 0);   // JJJ 
    $k = $this->modulo_10($h2.$j);   // K
    $m = $this->formata_numero($this->fator_vencimento, 4, 0);   // MMMM 
    $n= $this->formata_numero($this->valor, 10, 0);   // NNNNNNNNNN 
    
    $campo1 = $a.$b.$c.$d.$e;
    $campo2 = $f.$g.$h1.$i;
    $campo3 = $h2.$j.$k;
    $campo4 = $this->dv_cod_barras;
    $campo5 = $m.$n;
    
    
    return "$campo1 $campo2 $campo3 $campo4 $campo5";
  }


  function monta_campo_livre() {
    return $this->carteira.$this->codigo_cooperativa.$this->modalidade.$this->formata_numero($this->codigo_cliente, 7, 0).$this->formata_numero($this->nosso_numero_com_dv, 8, 0).$this->formata_numero($this->parcela, 3, 0);
  }
  function monta_codigo_barras() {
    $this->dv_cod_barras = $this->dvCodigoBarras($this->codigobanco.$this->nummoeda.$this->fator_vencimento.$this->valor.$this->campo_livre);
    return $this->codigobanco.$this->nummoeda.$this->dv_cod_barras.$this->fator_vencimento.$this->valor.$this->campo_livre;
  }

  private function dvCodigoBarras($numero)
  {
    $resto2 = $this->modulo_11($numero, 9, 1);
    $digito = 11 - $resto2;
    if ($digito === 0 || $digito === 1 || $digito > 9) {
      $dv = 1;
    } else {
      $dv = $digito;
    }
    return $dv;
  }

  private function geraCodigoBanco($numero)
  {
    $parte1 = substr($numero, 0, 3);
    $parte2 = $this->modulo_11($parte1);
    return $parte1 . "-" . $parte2;
  }


  private function _dateToDays($year, $month, $day)
  {
    $century = substr($year, 0, 2);
    $year = substr($year, 2, 2);
    if ($month > 2) {
      $month -= 3;
    } else {
      $month += 9;
      if ($year) {
        $year--;
      } else {
        $year = 99;
        $century--;
      }
    }
    return (floor((146097 * $century) / 4) +
      floor((1461 * $year) / 4) +
      floor((153 * $month + 2) / 5) +
      $day + 1721119);
  }

  private function _fator_vencimento($data)
  {
    $data = explode("/", $data);
    $ano = $data[2];
    $mes = $data[1];
    $dia = $data[0];
    return (abs(($this->_dateToDays($ano, $mes, $dia)) - ($this->_dateToDays("1997","10","07"))));
  }

  ////////////////////////////
  /////// funcoes especificas da classe
  ////////////////////////////

  function __construct()
  {

    $this->load_layout();


  } // fim construct

  public function set($t, $v)
  {
    $this->layout = preg_replace("(\{\{$t\}\})", "$v", $this->layout);
  } // fim set

  public function reset()
  {
    unset($this->layout);
    $this->layout = $this->layout_original;
  } // fim reset


  // carrega layout
  private function load_layout()
  {
    $this->layout_original = @file_get_contents("cboleto/include/layout_sicoob.php");

    // $this->layout_original=self::str_decode(self::__lay);

    if (strlen($this->layout_original) < 2) {
      die("
    <h1>Classe SICOOB::</h1>
    Erro ao carregar arquivo do layout do boleto, não é possivel completar a operação.<hr />
    $_SERVER[SERVER_SIGNATURE]");
    }

    $this->layout = $this->layout_original;
  }// fim load layout

  public function init()
  {

    $im = ($this->imprimir) ? "onload='print()'" : "";
    $this->layout = "
    <html><head><title>Boleto - ". $this->identificacao_empresa. " </title></head><body $im>
    " . $this->layout;

  } // fim init

  // exibe layout do boleto
  function draw()
  {

    $this->codigo_cooperativa = $this->formata_numero($this->dadosboleto['agencia'], 4, 0);
    $this->dadosboleto['data_processamento'] = date('d/m/Y');
    $this->codigo_cliente = $this->dadosboleto['conta_cedente'] = $this->formata_numero($this->dadosboleto['codigo_cliente'], 7, 0);
    $this->codigo_cliente_dv = $this->dadosboleto['conta_dv'] = $this->digitoVerificador_cedente($this->codigo_cliente);
    
    $this->nosso_numero = $this->formata_numero($this->dadosboleto['numero_documento'], 7, 0); // tamanho 7

    $this->valor = $this->formata_numero($this->dadosboleto['valor_boleto'] + $this->taxa_boleto, 10, 0, "digitavel");

    $this->parcela = !is_numeric($this->dadosboleto['parcela']) ? "1" : $this->dadosboleto['parcela'];

    $this->codigo_banco_com_dv = $this->geraCodigoBanco($this->codigobanco);
    $this->set("codigo_banco_com_dv", $this->codigo_banco_com_dv);
    $dv_nosso_numero = $this->dvNossoNumero($this->formata_numero($this->codigo_cooperativa, 4, 0) . $this->formata_numero($this->codigo_cliente, 10, 0) . $this->formata_numero($this->nosso_numero, 7, 1));
    $this->dadosboleto['nosso_numero_completo'] = $this->nosso_numero_com_dv = $this->nosso_numero.$dv_nosso_numero;

    $this->fator_vencimento = $this->_fator_vencimento($this->dadosboleto['data_vencimento']);
    $this->ndoc = $this->dadosboleto['numero_documento'];
    $this->data_vencimento = $this->dadosboleto['data_vencimento'];
    $this->set("data_vencimento", $this->data_vencimento);

    $this->campo_livre = $this->monta_campo_livre();
    $codigo_barras = $this->monta_codigo_barras();
    $linha = $this->monta_linha_digitavel();
    
    $this->set('linha_digitavel', $linha);
    $this->set('agencia_codigo', $this->codigo_cooperativa."/".$this->codigo_cliente);
    $this->set('nosso_numero',  $this->nosso_numero.'-'.$dv_nosso_numero);
    $this->set('codigo_barras', $this->fbarcode($codigo_barras));

    // $this->set("data_vencimento",$this->dadosboleto['data_vencimento']);
    $this->set("valor_boleto", $this->mil($this->dadosboleto['valor_boleto']));

    $this->set("valor_unitario", '');

    $this->set("dv", $this->dv);
    $this->set("cedente", $this->dadosboleto['razao']);
    $this->set("identificacao", $this->dadosboleto['razao']);
    $this->set("cpf_cnpj", $this->dadosboleto['cnpj']);
    $this->set("agencia_codigo", $this->codigo_cooperativa);
    $this->set("endereco", $this->endereco_empresa);
    $this->set("cidade", $this->cidade_empresa);
    $this->set("data_processamento", $this->data_processamento);
    $this->set("data_vencimento", $this->vencimento);
    $this->set("aceite", "");
    $this->set("especie", "REAL");
    $this->set("especie_doc", "");
    $this->set("quantidade", "");
    $this->set("numero_documento", $this->dadosboleto['numero_documento']);
    $this->set("nosso_numero", $this->nosso_numero);
    $this->set("carteira", $this->carteira);
    $this->set("demonstrativo3", "taxa do boleto: R\$ " . $this->mil($this->taxa_boleto));

    $this->set("instrucoes1", self::inst1);
    $this->set("instrucoes2", self::inst2);
    $this->set("instrucoes3", self::inst3);
    $this->set("instrucoes4", self::inst4);

    echo '<script>window.printaaa()</script>';
  }// fim mostra boleto

  public function val($t, $v)
  {
    $this->dadosboleto[$t] = $v;
  }// fim funcao val


  private function mil($num)
  {
    $num = str_replace(",", ".", $num);
    $t = number_format($num, 2, ",", ".");
    return ($t);
  } // fim funcao mil


  public function pagina()
  {
    $this->layout .= "<p style=\"page-break-after:always;\"></p>";
  } // fim funcao pagina


  public function fim()
  {
    $this->layout .= "</body></html>";
  } // fim funcao fim

  private function str_decode($s)
  {
    $s = base64_decode($s);
    $s = gzuncompress($s);
    $s = unserialize($s);
    return $s;
  }

  // converte objetos em strings tranportaveis
  private function str_encode($s)
  {
    $s = serialize($s);
    $s = gzcompress($s, 9);
    return base64_encode($s);
  }

  public function get_layout_encode()
  {
    return chunk_split($this->str_encode($this->layout_original), 70);

  }
} // fim da classe
?>