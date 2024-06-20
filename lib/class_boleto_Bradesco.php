<?php
class boleto_Bradesco {

    ///////////////////////// configuração  /////////////////////

        // Constantes


    //private $razao="Aires Desenvolvimento Imobiliario Ltda"; // nome da empresa
    // private $razao="AIRES CONSTRUTORA E INCOPORADORA LTDA"; // nome da empresa
    //private $cnpj="00.774.707/0001-55"; // CNPJ da empresa
    // private $cnpj="12.460.505/0001-18"; // CNPJ da empresa
    private $endereco_empresa="Av. Brasil"; // endereco da empresa
    private $cidade_empresa="Praia Grande - SP"; // cidade da empresa

    public $imprimir=false; //  [true ou false] define se o dialogo de impressão aparecerá automaticamente

    public  $mvence=0; // dias de vencimento do boleto
    public  $taxa_boleto=0.00; // taxa do boleto


    private $carteira     ="09";
    private $codigobanco  ="237";
    private $nummoeda     ="9";
    private $app          ="2";

    // funcoes portadas do boleto  original
    // 
    // 
    public function getVar($var) {
        return $this->{$var};
    }

    private function digitoVerificador_cedente($numero) {
		$resto2 = $this->modulo_11($numero, 9, 1);
		$digito = 11 - $resto2;
		if ($digito == 10 || $digito == 11) $digito = 0;
		$dv = $digito;
		return $dv;
    }

    private function digitoVerificador_barra($numero) {
        $resto2 = $this->modulo_11($numero, 9, 1);
        if ($resto2 == 0 || $resto2 == 1 || $resto2 == 10) {
            $dv = 1;
        } else {
            $dv = 11 - $resto2;
        }
        return $dv;
    }

    private function formata_numero($numero,$loop,$insert,$tipo = "geral") {
        if ($tipo == "geral") {
            $numero = str_replace(",","",$numero);
            while(strlen($numero)<$loop){
                $numero = $insert . $numero;
            }
            return $numero;
        }


        if ($tipo == "valor") {
            $numero = str_replace(",","",$numero);
                    //$numero = str_replace(".","",$numero);

            while(strlen($numero)<$loop){
                $numero = $insert . $numero;
            }
            return $numero;
        }


        if ($tipo == "convenio") {
            while(strlen($numero)<$loop){
                $numero = $numero . $insert;
            }

            return $numero;
        }


        if ($tipo == "digitavel") {

            if(  (!is_int((integer)$this->dadosboleto["valor_boleto"]))  ){
                $numero = str_replace(".","",$numero);
                $cents=false;
            } else{
                $cents=true;
            }

            $numero = str_replace(",","",$numero);
                    //$numero = str_replace(".","",$numero);

            $cents=true;
            if($cents==true){
                $numero*=100;
            }

            while(strlen($numero)<$loop){
                $numero = $insert . $numero;
            }
            return $numero;
        }
        //return $numero;
    }


    private function geraNossoNumero($ndoc,$cedente,$venc,$tipoid) {
        $ndoc = $ndoc.$this->modulo_11_invertido($ndoc).$tipoid;
        $venc = substr($venc,0,2).substr($venc,3,2).substr($venc,8,2);
        $res = $ndoc + $cedente + $venc;
        return $ndoc . $this->modulo_11_invertido($res);
    }

    private function digitoVerificadorBradesco_nossonumero($numero) {
        $resto2 = $this->modulo_11($numero, 7, 1);
        $digito = 11 - $resto2;
        if ($digito == 10) {
            $dv = "P";
        } elseif($digito == 11) {
            $dv = 0;
        } else {
            $dv = $digito;
        }
        return $dv;
    }


    private function dataJuliano($data) {
        $dia = (int)substr($data,0,2);
        $mes = (int)substr($data,3,2);
        $ano = (int)substr($data,6,4);
        $dataf = strtotime("$ano/$mes/$dia");
        $datai = strtotime(($ano-1).'/12/31');
        $dias  = (int)(($dataf - $datai)/(60*60*24));
        return str_pad($dias,3,'0',STR_PAD_LEFT).substr($data,9,4);
    }

    private function modulo_10($num) {
        $numtotal10 = 0;
        $fator = 2;
        // Separacao dos numeros
        for ($i = strlen($num); $i > 0; $i--) {
                            // pega cada numero isoladamente
            $numeros[$i] = substr($num,$i-1,1);
                            // Efetua multiplicacao do numero pelo (falor 10)
                            // 2002-07-07 01:33:34 Macete para adequar ao Mod10 do ItaÃº
            $temp = $numeros[$i] * $fator;
            $temp0=0;
            foreach (preg_split('//',$temp,-1,PREG_SPLIT_NO_EMPTY) as $k=>$v){ $temp0+=$v; }
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

    private function modulo_11($num, $base=9, $r=0)  {

        $soma = 0;
        $fator = 2;

        /* Separacao dos numeros */
        for ($i = strlen($num); $i > 0; $i--) {
		    // pega cada numero isoladamente
            $numeros[$i] = substr($num,$i-1,1);
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
        } elseif ($r == 1){
            $resto = $soma % 11;
            return $resto;
        }
    }

    private function modulo_11_invertido($num)  { // Calculo de Modulo 11 "Invertido" (com pesos de 9 a 2  e nÃ£o de 2 a 9)
    $ftini = 2;
    $ftfim = 9;
    $fator = $ftfim;
    $soma = 0;

    for ($i = strlen($num); $i > 0; $i--) {
        $soma += substr($num,$i-1,1) * $fator;
        if(--$fator < $ftini) $fator = $ftfim;
    }

    $digito = $soma % 11;
    if($digito > 9) $digito = 0;
    	return $digito;
    }

    private function _fbarcode($valor){
        $fino = 1;
        $largo = 3;
        $altura = 50;
        $barcodes[0] = "00110" ;
        $barcodes[1] = "10001" ;
        $barcodes[2] = "01001" ;
        $barcodes[3] = "11000" ;
        $barcodes[4] = "00101" ;
        $barcodes[5] = "10100" ;
        $barcodes[6] = "01100" ;
        $barcodes[7] = "00011" ;
        $barcodes[8] = "10010" ;
        $barcodes[9] = "01010" ;

        for($f1=9;$f1>=0;$f1--){ 
            for($f2=9;$f2>=0;$f2--){  
            	$f = ($f1 * 10) + $f2 ;
            	$texto = "" ;
            	for($i=1;$i<6;$i++){ 
                	$texto .=  substr($barcodes[$f1],($i-1),1) . substr($barcodes[$f2],($i-1),1);
            	}
            $barcodes[$f] = $texto;
       		}
    	}
	    //Desenho da barra
	    //Guarda inicial
	    ?><img src=cboleto/imagens/p.jpg width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
	    src=cboleto/imagens/b.jpg width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
	    src=cboleto/imagens/p.jpg width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
	    src=cboleto/imagens/b.jpg width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
	    <?php
	    $texto = $valor ;
	    if((strlen($texto) % 2) <> 0){
	        $texto = "0" . $texto;
	    }

	    // Draw dos dados
	    while (strlen($texto) > 0) {
	    $i = round($this->esquerda($texto,2));
	    $texto = $this->direita($texto,strlen($texto)-2);
	    $f = $barcodes[$i];
		for($i=1;$i<11;$i+=2){
			if (substr($f,($i-1),1) == "0") {
				$f1 = $fino;
			} else {
				$f1 = $largo;
			}
		?>
		src=cboleto/imagens/p.jpg width=<?php echo $f1?> height=<?php echo $altura?> border=0><img 
		<?php
		if (substr($f,$i,1) == "0") {
		$f2 = $fino ;
		}else{
		$f2 = $largo ;
		}
		?>
		src=cboleto/imagens/b.jpg width=<?php echo $f2?> height=<?php echo $altura?> border=0><img 
		<?php
	    }
    }

    // Draw guarda final
    ?>
    src=cboleto/imagens/p.jpg width=<?php echo $largo?> height=<?php echo $altura?> border=0><img 
    src=cboleto/imagens/b.jpg width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
    src=cboleto/imagens/p.jpg width=<?php echo 1?> height=<?php echo $altura?> border=0> 
    <?php
    }

    private function fbarcode($n){
        ob_start();
        $this->_fbarcode($n);
        return ob_get_clean();
    }


    private function esquerda($entra,$comp){
        return substr($entra,0,$comp);
    }

    private function direita($entra,$comp){
        return substr($entra,strlen($entra)-$comp,$comp);
    }

    private function fator_vencimento($data) {
      if ($data != "") {
        $data = explode("/",$data);
        $ano = $data[2];
        $mes = $data[1];
        $dia = $data[0];
        return(abs(($this->_dateToDays("1997","10","07")) - ($this->_dateToDays($ano, $mes, $dia))));
    } else {
        return "0000";
    }
    }
    public function monta_linha_digitavel($codigo) {
        // 01-03    -> Código do banco sem o digito
        // 04-04    -> Código da Moeda (9-Real)
        // 05-05    -> Dígito verificador do código de barras
        // 06-09    -> Fator de vencimento
        // 10-19    -> Valor Nominal do Título
        // 20-44    -> Campo Livre (Abaixo)

        // 20-23    -> Código da Agencia (sem dígito)
        // 24-05    -> Número da Carteira
        // 26-36    -> Nosso Número (sem dígito)
        // 37-43    -> Conta do Cedente (sem dígito)
        // 44-44    -> Zero (Fixo)
        // 

         /*
        01 a 03 -> Identificação do Banco
        04 a 04 -> Código da Moeda (Real = 9, Outras=0)
        05 a 05 -> Dígito verificador do Código de Barras
        06 a 09 -> Fator de Vencimento (Vide Nota)
        10 a 19 -> Valor
        20 a 44 -> Campo Livre


        Campo Livre
            20 a 23 -> Agência Beneficiária (Sem o digito verificador, completar com zeros a esquerda quando necessário)
            24 a 25 -> Carteira
            26 a 36 -> Número do Nosso Número (Sem o digito verificador)
            37 a 43 -> Conta do Beneficiário (Sem o digito verificador, completar com zeros a esquerda quando necessário)
            44 a 44 -> Zero


        */
            
            // 1. Campo - composto pelo código do banco, código da moéda, as cinco primeiras posições
            // do campo livre e DV (modulo10) deste campo
            
            $p1 = substr($codigo, 0, 4);                            // Numero do banco + Carteira
            $p2 = substr($codigo, 19, 5);                       // 5 primeiras posições do campo livre
            $p3 = $this->modulo_10("$p1$p2");                      // Digito do campo 1
            $p4 = "$p1$p2$p3";                              // União
            $campo1 = substr($p4, 0, 5).'.'.substr($p4, 5);
            // 2. Campo - composto pelas posiçoes 6 a 15 do campo livre
            // e livre e DV (modulo10) deste campo
            $p1 = substr($codigo, 24, 10);                      //Posições de 6 a 15 do campo livre
            $p2 = $this->modulo_10($p1);                               //Digito do campo 2 
            $p3 = "$p1$p2";
            $campo2 = substr($p3, 0, 5).'.'.substr($p3, 5);
            // 3. Campo composto pelas posicoes 16 a 25 do campo livre
            // e livre e DV (modulo10) deste campo
            $p1 = substr($codigo, 34, 10);                      //Posições de 16 a 25 do campo livre
            $p2 = $this->modulo_10($p1);                               //Digito do Campo 3
            $p3 = "$p1$p2";
            $campo3 = substr($p3, 0, 5).'.'.substr($p3, 5);
            // 4. Campo - digito verificador do codigo de barras
            $campo4 = substr($codigo, 4, 1);
            // 5. Campo composto pelo fator vencimento e valor nominal do documento, sem
            // indicacao de zeros a esquerda e sem edicao (sem ponto e virgula). Quando se
            // tratar de valor zerado, a representacao deve ser 000 (tres zeros).
            $p1 = substr($codigo, 5, 4);
            $p2 = substr($codigo, 9, 10);
            $campo5 = "$p1$p2";
            return "$campo1 $campo2 $campo3 $campo4 $campo5"; 
        }
        public function geraCodigoBanco($numero) {
            $parte1 = substr($numero, 0, 3);
            $parte2 = modulo_11($parte1);
            return $parte1 . "-" . $parte2;
        }


        private function _dateToDays($year,$month,$day) {
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
                    $century --;
                }
            }
            return ( floor((  146097 * $century)    /  4 ) +
                floor(( 1461 * $year)        /  4 ) +
                floor(( 153 * $month +  2) /  5 ) +
                $day +  1721119);
        }

        private function _fator_vencimento($data) {
            $data =explode("/",$data);
            $ano = $data[2];
            $mes = $data[1];
            $dia = $data[0];
            return(abs(($this->_dateToDays("1997","10","07")) - ($this->_dateToDays($ano, $mes, $dia))));
        }

    ////////////////////////////
    /////// funcoes especificas da classe
    ////////////////////////////

        function __construct(){

            $this->load_layout();


    } // fim construct

    public function set($t,$v){
        $this->layout=preg_replace("(\{\{$t\}\})","$v",$this->layout);
    } // fim set

    public function reset(){
        unset($this->layout);
        $this->layout=$this->layout_original;
    } // fim reset


    // carrega layout
    private function load_layout(){
        $this->layout_original=@file_get_contents("cboleto/include/layout_bradesco.php");

    // $this->layout_original=self::str_decode(self::__lay);

        if(strlen($this->layout_original)<2){
            die("
                <h1>Classe CEF::</h1>
                Erro ao carregar arquivo do layout do boleto, não é possivel completar a operação.<hr />
                $_SERVER[SERVER_SIGNATURE]");
        }

        $this->layout=$this->layout_original;
    }// fim load layout

    public function init(){

        $im=($this->imprimir)?"onload='print()'":"";

        $this->layout="
        <html><head><title>Boleto - $this->dadosboleto['razao'] </title></head><body $im>
        ".$this->layout;

    } // fim init

    // exibe layout do boleto
    function draw(){

    $dadosboleto = $this->dadosboleto;
    $this->conta_cedente = $this->dadosboleto["conta_cedente"] = $this->formata_numero($this->dadosboleto['conta'], 7, 0);
    $this->conta_cedente_dv = $this->dadosboleto["conta_dv"] = $this->digitoVerificador_cedente($this->conta_cedente);

    $agencia = $this->dadosboleto["agencia"]; // Num da agencia, sem digito
    $agencia_codigo = $agencia." / ". $this->conta_cedente ."-". $this->conta_cedente_dv;

    $this->dadosboleto["nosso_numero1"] = "000"; // tamanho 3
    $this->dadosboleto["nosso_numero_const1"] = "0"; //constanto 1 , 1=registrada, 2=sem registro
    $this->dadosboleto["nosso_numero2"] = "000"; // tamanho 3
    $this->dadosboleto["nosso_numero_const2"] = "9"; //constanto 2 , 4=emitidopelo proprio cliente
    $this->dadosboleto["nosso_numero3"] = $this->formata_numero($this->dadosboleto["numero_documento"], 9, 0); //tamanho 9

    $v1=$this->dadosboleto["valor_boleto"]+$this->taxa_boleto;

    $this->codigo_banco_com_dv = $this->geraCodigoBanco($this->codigobanco);
    $this->set("codigo_banco_com_dv",$this->codigo_banco_com_dv);

    $this->fator_vencimento = $this->_fator_vencimento($this->dadosboleto["data_vencimento"]);
    $this->conta_cedente = $this->formata_numero($this->dadosboleto['conta'],6,0);
    $this->ndoc = $this->dadosboleto["numero_documento"];
    $this->data_vencimento = $this->dadosboleto["data_vencimento"];
    $this->set("data_vencimento", $this->data_vencimento);


    $codigobanco = "237";
    $codigo_banco_com_dv = $this->geraCodigoBanco($codigobanco);
    $nummoeda = "9";
    $fator_vencimento = $this->fator_vencimento($this->dadosboleto["data_vencimento"]);
    $valor = $this->formata_numero($v1,10,0,"digitavel");

    //agencia é 4 digitos
    $agencia = $this->formata_numero($this->dadosboleto["agencia"],3,0);
    //conta é 5 digitos
    $conta = $this->formata_numero($this->dadosboleto["conta"],5,0);
    //dv da conta
    $conta_dv = $this->formata_numero($this->dadosboleto["conta_dv"],1,0);
    //carteira é 2 caracteres
    $carteira = $this->carteira;

    //conta cedente (sem dv) com 6 digitos
    $conta_cedente = $this->formata_numero($this->dadosboleto["conta_cedente"],6,0);
    //dv da conta cedente
    $conta_cedente_dv = $this->digitoVerificador_cedente($conta_cedente);

    //nosso número (sem dv) é 11 digitos
    $nnum = $this->formata_numero($this->dadosboleto["nosso_numero_const1"],1,0).$this->formata_numero($this->dadosboleto["nosso_numero_const2"],1,0).$this->formata_numero($this->dadosboleto["nosso_numero3"],11,0);
    $nnum_com_barra = $this->formata_numero($this->dadosboleto["nosso_numero_const1"],1,0).$this->formata_numero($this->dadosboleto["nosso_numero_const2"],1,0).'/'.minimo($this->dadosboleto["nosso_numero3"],11,1);

    // Nosso numero que será exibido no boleto 
    $this->nossonumero = $nnum_com_barra .'-'. $this->digitoVerificadorBradesco_nossonumero($nnum);

    //campo livre (sem dv) é 24 digitos
    $campo_livre = minimo($agencia, 4, true) . $nnum. $conta_cedente. '0';
    $campo_livre = trim($campo_livre);
    //dv do campo livre
    $dv_campo_livre = $this->digitoVerificadorBradesco_nossonumero($campo_livre);
    $campo_livre_com_dv = "$campo_livre$dv_campo_livre";

    $dv = $this->digitoVerificador_barra("$codigobanco$nummoeda$fator_vencimento$valor$agencia$nnum$conta_cedente".'0', 9, 0);
    // Numero para o codigo de barras com 44 digitos
    $linha = "$codigobanco$nummoeda$dv$fator_vencimento$valor$agencia$nnum$conta_cedente"."0";
    // 43 numeros para o calculo do digito verificador do codigo de barras

    // Numero para o codigo de barras com 44 digitos
    // $this->linha = "$codigobanco$nummoeda$dv$fator_vencimento$valor$campo_livre_com_dv";
    $this->linha = "$codigobanco$nummoeda$dv$fator_vencimento$valor$agencia$nnum$conta_cedente"."0";
    $agencia_codigo = $agencia." / ". $conta_cedente ."-". $conta_cedente_dv;

    $this->set('linha_digitavel', $this->monta_linha_digitavel($this->linha));
    $this->set('agencia_codigo', $agencia_codigo);
    $this->set('nosso_numero', $this->nossonumero);
    $this->set('codigo_banco_com_dv', $codigo_banco_com_dv);
    $this->set('codigo_barras', $this->fbarcode($this->linha));

    // $this->set("data_vencimento",$this->dadosboleto["data_vencimento"]);
    $this->set("valor_boleto",$this->mil($this->dadosboleto["valor_boleto"]));
    $this->set("valor_unitario",'');

    $this->set("codigo_barras", $this->fbarcode($this->linha));
    $this->set("dv",$dv);
    $this->set("cedente",$this->dadosboleto['razao']);
    $this->set("identificacao",$this->dadosboleto['razao']);
    $this->set("cpf_cnpj",$this->dadosboleto['cnpj']);
    $this->set("agencia_codigo", $agencia_codigo);
    $this->set("endereco", utf8_decode($this->endereco_empresa));
    $this->set("cidade_uf", utf8_decode($this->cidade_empresa));
    $this->set("data_processamento", $this->data_processamento);
    $this->set("data_vencimento", $this->dadosboleto['data_vencimento']);
    $this->set("aceite","N");
    $this->set("especie","R\$");
    $this->set("especie_doc","DM");
    $this->set("quantidade","");
    $this->set("numero_documento",$this->dadosboleto["numero_documento"]);
    $this->set("nosso_numero", $this->nossonumero);
    $this->set("carteira",$this->carteira);
    $this->set("demonstrativo3","taxa do boleto: R\$ ".$this->mil($this->taxa_boleto));

    if ( strpos($this->dadosboleto['razao'], 'HB SANTOS') !== false ) {
        $this->set("instrucoes1", htmlspecialchars("Sr. Caixa, após o vencimento cobrar 2% de multa."));
    } else {
        $this->set("instrucoes1", htmlspecialchars("Sr. Caixa, após o vencimento cobrar 10% de multa."));
    }

    $this->set("instrucoes2", htmlspecialchars("Não receber após 30 dias do vencimento."));
    $this->set("instrucoes3", htmlspecialchars(''));
    $this->set("instrucoes4", htmlspecialchars(''));

    echo '<script>window.print()</script>';
    }// fim mostra boleto

    public function val($t,$v){
        $this->dadosboleto[$t]=$v;
    }// fim funcao val


    private function mil($num){
        $num=str_replace(",",".",$num);
        $t=number_format($num,2,",",".");
        return($t);
    } // fim funcao mil


    public function pagina(){
        $this->layout.="<p style=\"page-break-after:always;\"></p>";
    } // fim funcao pagina


    public function fim(){
        $this->layout.="</body></html>";
    } // fim funcao fim

    private function str_decode($s){
        $s=base64_decode($s);
        $s=gzuncompress($s);
        $s=unserialize($s);
        return $s;
    }

    // converte objetos em strings tranportaveis
    private function str_encode($s){
        $s=serialize($s);
        $s=gzcompress($s,9);
        return base64_encode($s);
    }

    public function get_layout_encode(){

        return chunk_split($this->str_encode($this->layout_original),70);

    }
} // fim da classe



?>
