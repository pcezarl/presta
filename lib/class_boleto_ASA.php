<?php

/**
 * Boleto do Banco ASA SCD (594) — carteira 121, Cobranca Vinculada.
 * O calculo vive em asa_calc; esta classe so preenche o layout.
 */
class boleto_ASA {

    const inst1 = "Sr. Caixa, apos vencimento cobrar multa de 2%";
    const inst2 = "Nao receber apos 30 dias do vencimento.";
    const inst3 = "";
    const inst4 = "";

    public $layout;
    public $layout_original;
    public $dadosboleto = array();
    public $imprimir;

    public function __construct() {
        $this->load_layout();
    }

    private function load_layout() {
        $this->layout_original = @file_get_contents("cboleto/include/layout_asa.php");
        if (strlen($this->layout_original) < 2) {
            die("
            <h1>Classe ASA::</h1>
            Erro ao carregar arquivo do layout do boleto, nao e possivel completar a operacao.<hr />
            $_SERVER[SERVER_SIGNATURE]");
        }
        $this->layout = $this->layout_original;
    }

    public function init() {
        $im = ($this->imprimir) ? "onload='print()'" : "";
        $this->layout = "
        <html><head><title>Boleto - Banco ASA SCD</title></head><body $im>
        " . $this->layout;
    }

    public function val($t, $v) {
        $this->dadosboleto[$t] = $v;
    }

    public function set($t, $v) {
        $this->layout = preg_replace("(\{\{$t\}\})", "$v", $this->layout);
    }

    public function reset() {
        unset($this->layout);
        $this->layout = $this->layout_original;
    }

    public function pagina() {
        $this->layout .= "<p style=\"page-break-after:always;\"></p>";
    }

    public function fim() {
        $this->layout .= "</body></html>";
    }

    private function mil($num) {
        $num = str_replace(",", ".", $num);
        return number_format($num, 2, ",", ".");
    }

    public function draw() {
        $agencia  = str_pad($this->dadosboleto['agencia'], 4, '0', STR_PAD_LEFT);
        $carteira = str_pad($this->dadosboleto['carteira'], 3, '0', STR_PAD_LEFT);
        $operacao = str_pad($this->dadosboleto['operacao'], 7, '0', STR_PAD_LEFT);
        $nn       = $this->dadosboleto['nosso_numero'];

        $dv_nn = asa_calc::dv_nosso_numero($agencia, $carteira, $nn);
        $this->dadosboleto['nosso_numero_completo'] = asa_calc::nosso_numero_com_dv($agencia, $carteira, $nn);

        $campo_livre = asa_calc::campo_livre($agencia, $carteira, $operacao, $nn);
        $fator       = asa_calc::fator_vencimento($this->dadosboleto['data_vencimento']);
        $valor10     = asa_calc::valor_centavos($this->dadosboleto['valor_boleto'], 10);
        $dv_barras   = asa_calc::dv_codigo_barras($campo_livre, $fator, $valor10);

        $codigo_barras = asa_calc::codigo_barras($campo_livre, $fator, $valor10);
        $this->dadosboleto['linha_digitavel'] = asa_calc::linha_digitavel($campo_livre, $fator, $valor10, $dv_barras);

        $nn_exibicao = str_pad($nn, 7, '0', STR_PAD_LEFT) . '-' . $dv_nn;

        $this->set("codigo_banco_com_dv", '594-0');
        $this->set("linha_digitavel", $this->dadosboleto['linha_digitavel']);
        $this->set("codigo_barras", $this->fbarcode($codigo_barras));
        $this->set("agencia_codigo", $agencia . '-' . $this->dadosboleto['agencia_dv']
                                   . ' / ' . $this->dadosboleto['conta'] . '-' . $this->dadosboleto['conta_dv']);
        $this->set("nosso_numero", $nn_exibicao);
        $this->set("carteira", $carteira);
        $this->set("data_vencimento", $this->dadosboleto['data_vencimento']);
        $this->set("data_processamento", date('d/m/Y'));
        $this->set("valor_boleto", $this->mil($this->dadosboleto['valor_boleto']));
        $this->set("cedente", $this->dadosboleto['razao']);
        $this->set("identificacao", $this->dadosboleto['razao']);
        $this->set("cpf_cnpj", $this->dadosboleto['cnpj']);
        $this->set("numero_documento", $this->dadosboleto['numero_documento']);
        $this->set("especie", "R\$");
        $this->set("especie_doc", "DM");
        $this->set("aceite", "N");
        $this->set("quantidade", "");
        $this->set("valor_unitario", "");
        $this->set("demonstrativo3", "");
        $this->set("instrucoes1", self::inst1);
        $this->set("instrucoes2", self::inst2);
        $this->set("instrucoes3", self::inst3);
        $this->set("instrucoes4", self::inst4);
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
}
