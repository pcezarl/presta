<?php
//error_reporting(E_ALL);


class boleto_HSBC{

///////////////////////// configuração  ////////////////////


private $identificacao_empresa="Construtora Aires"; // nome da empresa
private $cnpj_empresa="1234234"; // CNPJ da empresa
private $endereco_empresa="viela das quebradas,666"; // endereco da empresa
private $cidade_empresa="Praia Grande - SP"; // cidade da empresa
private $codigo_empresa="8888888";  // codigo do banco (7 digitos)


public  $mvence=7; // dias de vencimento do boleto
public  $taxa_boleto=2.5; // taxa do boleto


// configuracao das instrucoes do boleto
const inst1="Sr. Caixa, não receber após o vencimento";
const inst2="";
const inst3="";
const inst4="";

//////////////////////////////////// layout ////////////////////////
const _layout="
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
pqA=
";
//////////////////////////////fim layout///////////////////////////////


private $carteira="CNR";
private $codigobanco  = "399";
private $nummoeda     = "9";
private $app          = "2"; 

////////////////////////////////////////////////////////////

// funcoes portadas do boleto  original
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
    }
    if ($tipo == "valor") {
        $numero = str_replace(",","",$numero);
        while(strlen($numero)<$loop){
            $numero = $insert . $numero;
        }
    }
    if ($tipo == "convenio") {
        while(strlen($numero)<$loop){
            $numero = $numero . $insert;
        }
    }
    return $numero;
}

private function geraNossoNumero($ndoc,$cedente,$venc,$tipoid) {
    $ndoc = $ndoc.$this->modulo_11_invertido($ndoc).$tipoid;
    $venc = substr($venc,0,2).substr($venc,3,2).substr($venc,8,2);
    $res = $ndoc + $cedente + $venc;
    return $ndoc . $this->modulo_11_invertido($res);
}

private function digitoVerificador_nossonumero($numero) {
    $resto2 = modulo_11($numero, 9, 1);
     $digito = 11 - $resto2;
     if ($digito == 10 || $digito == 11) {
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

private function geraCodigoBanco($numero) {
    $parte1 = substr($numero, 0, 3);
    $parte2 = $this->modulo_11($parte1);
    return $parte1 . "-" . $parte2;
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
} //fim mod11

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

$fino = 1 ;
$largo = 3 ;
$altura = 50 ;

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
?><img src=imagens/p.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
src=imagens/b.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
src=imagens/p.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
src=imagens/b.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
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
      $f1 = $fino ;
    }else{
      $f1 = $largo ;
    }
?>
    src=imagens/p.png width=<?php echo $f1?> height=<?php echo $altura?> border=0><img 
<?php
    if (substr($f,$i,1) == "0") {
      $f2 = $fino ;
    }else{
      $f2 = $largo ;
    }
?>
    src=imagens/b.png width=<?php echo $f2?> height=<?php echo $altura?> border=0><img 
<?php
  }
}

// Draw guarda final
?>
src=imagens/p.png width=<?php echo $largo?> height=<?php echo $altura?> border=0><img 
src=imagens/b.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
src=imagens/p.png width=<?php echo 1?> height=<?php echo $altura?> border=0> 
  <?php
} //Fim da funÃ§Ã£o

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

private function monta_linha_digitavel($codigo) {
    
    $campo1 = substr($codigo,0,4) . substr($codigo,19,5);
    $campo1 = $campo1 . $this->modulo_10($campo1);
    $campo1 = substr($campo1,0,5) . '.' . substr($campo1,5,5);
    
    // 2. Campo - composto pelas posiÃ§oes 6 a 15 do campo livre
    // e livre e DV (modulo10) deste campo
    $campo2 = substr($codigo,24,2) . substr($codigo,26,8);
    $campo2 = $campo2 . $this->modulo_10($campo2);
    $campo2 = substr($campo2,0,5) . '.' . substr($campo2,5,6);
    
    // 3. Campo composto pelas posicoes 16 a 25 do campo livre
    // e livre e DV (modulo10) deste campo
    $campo3 = substr($codigo,34,5) . substr($codigo,39,4) . substr($codigo,43,1);
    $campo3 = $campo3 . $this->modulo_10($campo3);
    $campo3 = substr($campo3,0,5) . '.' . substr($campo3,5,6);

    // 4. Campo - digito verificador do codigo de barras
    $campo4 = substr($codigo, 4, 1);
    
    // 5. Campo composto pelo fator vencimento e valor nominal do documento, sem
    // indicacao de zeros a esquerda e sem edicao (sem ponto e virgula). Quando se
    // tratar de valor zerado, a representacao deve ser 000 (tres zeros).
    $campo5 = substr($codigo, 5, 4) . substr($codigo, 9, 10);
    
    return "$campo1 $campo2 $campo3 $campo4 $campo5"; 
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
    $data = split("/",$data);
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

private function load_layout(){
//$this->layout_original=@file_get_contents("tpl_layout_hsbc.html");

$this->layout_original=$this->str_decode(self::_layout);

$this->layout=$this->layout_original;
}// fim load layout

public function init(){
$this->layout="
<html><head><title>Boletos</title></head><body>
".$this->layout;

} // fim init

// exibe layout do boleto
function draw(){
$this->valor = $this->formata_numero($this->dadosboleto["valor_boleto"]+$this->taxa_boleto,10,0,"valor"); 
$this->codigo_banco_com_dv = $this->geraCodigoBanco($this->codigobanco); 
$this->set("codigo_banco_com_dv",$this->codigo_banco_com_dv);

$this->fator_vencimento = $this->_fator_vencimento($this->dadosboleto["data_vencimento"]);
//$this->carteira = $this->dadosboleto["carteira"];
$this->codigocedente = $this->formata_numero($this->codigo_empresa,7,0);
$this->ndoc = $this->dadosboleto["numero_documento"];
$this->vencimento = $this->dadosboleto["data_vencimento"];
$this->nnum = $this->formata_numero($this->dadosboleto["numero_documento"],13,0);
$this->nossonumero = $this->geraNossoNumero($this->nnum,$this->codigocedente,$this->vencimento,'4');
$this->nosso_numero = $this->geraNossoNumero($this->nnum,$this->codigocedente,$this->vencimento,'4');

$this->vencjuliano = $this->dataJuliano($this->vencimento);
$this->barra = $this->codigobanco.$this->nummoeda.$this->fator_vencimento.$this->valor.$this->codigocedente.$this->nnum.$this->vencjuliano.$this->app;
$this->dv = $this->digitoVerificador_barra($this->barra, 9, 0);

$this->linha = substr($this->barra,0,4) . $this->dv . substr($this->barra,4);

$this->agencia_codigo = $this->codigocedente;


$this->set("barras",$this->fbarcode($this->linha));


$this->dadosboleto["codigo_barras"] = $this->linha;
$this->val("codigo_barras",$this->linha);

$this->dadosboleto["linha_digitavel"] = $this->monta_linha_digitavel($this->linha);
$this->set("linha_digitavel",$this->monta_linha_digitavel($this->linha));

$this->dadosboleto["agencia_codigo"] = $this->agencia_codigo;
$this->set("agencia_codigo",$this->agencia_codigo);

$this->set("data_vencimento",$this->dadosboleto["data_vencimento"]);

$this->set("valor_boleto",$this->mil($this->valor));
$this->set("valor_unitario",'');


$this->set("dv",$this->dv);


$this->set("cedente",$this->identificacao_empresa);
$this->set("identificacao",$this->identificacao_empresa);
$this->set("cnpj",$this->cnpj_empresa);
$this->set("endereco",$this->endereco_empresa);
$this->set("cidade",$this->cidade_empresa);
$this->set("data_processamento",date("d/m/Y"));
$this->set("aceite","");
$this->set("especie","R\$");
$this->set("especie_doc","");
$this->set("quantidade","");
$this->set("numero_documento",$this->dadosboleto["numero_documento"]);
$this->set("nosso_numero",$this->nosso_numero);
$this->set("carteira",$this->carteira);
$this->set("demonstrativo3","Taxa do boleto: R\$ ".$this->mil($this->taxa_boleto));

$this->set("instrucoes1",self::inst1);
$this->set("instrucoes2",self::inst2);
$this->set("instrucoes3",self::inst3);
$this->set("instrucoes4",self::inst4);


}// fim mostra boleto

public function val($t,$v){
$this->dadosboleto[$t]=$v;
}// fim funcao val


private function mil($num){
$num=str_replace(",",".",$num);
$t=number_format($num,2,",",".");
//$t=money_format('%.2n', $num);
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



} // fim da classe


?>