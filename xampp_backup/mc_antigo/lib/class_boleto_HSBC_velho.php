<?php
//error_reporting(E_ALL);


class boleto_HSBC{

///////////////////////// configuração  ////////////////////


private $identificacao_empresa="Construtora Aires"; // nome da empresa
private $cnpj_empresa="00.774.707/0001-55"; // CNPJ da empresa
private $endereco_empresa="Av. Brasil, 600 - sala 1009"; // endereco da empresa
private $cidade_empresa="Praia Grande - SP"; // cidade da empresa
private $codigo_empresa="3505901";  // codigo do banco (7 digitos)

public $imprimir=true; //  [true ou false] define se o dialogo de impressão aparecerá automaticamente

public  $mvence=7; // dias de vencimento do boleto
public  $taxa_boleto=0; // taxa do boleto


// configuracao das instrucoes do boleto
const inst1="Sr. Caixa, receber até 2 dias úteis após o vencimento";
const inst2="";
const inst3="";
const inst4="";


private $carteira     ="CNR";
private $codigobanco  ="399";
private $nummoeda     ="9";
private $app          ="2";


/////////////////////// layout /////////////////

const __lay="
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";

///////////////////////////////////////////////


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
                $numero = str_replace(".","",$numero);
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
//$this->layout_original=@file_get_contents("tpl_layout_hsbc.html");

$this->layout_original=self::str_decode(self::__lay);

if(strlen($this->layout_original)<2){
die("
<h1>Classe HSBC::</h1>
Erro ao carregar arquivo do layout do boleto, não é possivel completar a operação.<hr />
$_SERVER[SERVER_SIGNATURE]");
}

$this->layout=$this->layout_original;
}// fim load layout

public function init(){

$im=($this->imprimir)?"onload='print()'":"";

$this->layout="
<html><head><title>Boleto - $this->identificacao_empresa </title></head><body $im>
".$this->layout;

} // fim init

// exibe layout do boleto
function draw(){
$this->valor = $this->formata_numero($this->dadosboleto["valor_boleto"]+$this->taxa_boleto,10,0,"valor");
$this->codigo_banco_com_dv = $this->geraCodigoBanco($this->codigobanco);
$this->set("codigo_banco_com_dv",$this->codigo_banco_com_dv);

$this->fator_vencimento = $this->_fator_vencimento($this->dadosboleto["data_vencimento"]);
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