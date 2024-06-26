<?php

////////////////////////////
// conjunto de funcoes uteis
// arquivo: func.php
//
// by Valmir Campos - valmirez@hotmail.com
//
//
// 2004-2009
///////////////////////////

// limpa as variaveis para tentar evitar ataques
function limpa($s){
$res=array(
"select",
"insert",
"delete",
"drop",
"query",
"database",
"sql"
);

$s=strip_tags($s);
$s=preg_replace("[<>#%\^]","",$s);
$s=str_replace($res,'',$s);
return $s;

} // fim limpa

// testa o formato do email
function testmail($m){
return ereg("^(.{3,})@(.{3,})\\.(.+$)",$m);
} // fim do teste de email


// transforma valores do MySql para formato normal
function mil($num){
$num=str_replace(",",".",$num);
$t=number_format($num,2,",",".");
return($t);
}

// transforma formato de valores normais em formato MySql
function nformat($valor){
$valor=str_replace(".","",$valor); 
$valor=str_replace(",",".",$valor); 
$valor=number_format($valor, 2, '.', '');   
return($valor);
}



// pega a primeira palavra de uma frase
// geralmente usado para pegar o primeiro nome da pessoa
function pnome($n){
$t=explode(" ",$n);
return $t[0];
}


// converte data mysql em data normal
function mydata($d){
$data=strtotime($d);
$data=date("d/m/Y",$data);
return $data;
}

// converte data normal em data mysql
function data2mysql($d){
$data=str_replace("/","-",$d);
$data=strtotime($data);
$data=date("Y-m-d",$data);
return $data;
}

// mostra erros padronizados
function qerror($i){
$str[0]="O Capacitor de Fluxo está com a carga baixa, não é possivel efetuar a transferencia de dados.";
$str[1]="O sistema de teletransporte está com defeito, o envio dos dados não foi efetuado";
$str[2]="Ocorreu uma microfissura no núcleo de plasma, a energia está sendo direcionada ao suporte de vida.";
$str[3]="Uma tempestade derrubou a torre da antena do satelite, a comunicação está inoperante.";
$str[4]="O nível do fluxo de dados está muito baixo, não é possível concluir a irrigação.";
$str[5]="O condutor de plasma está entupido, o dispositivo transdimensional está inoperante.";
$str[6]="A Policia Federal apreendeu o onibus que transportava as informações, não é possivel importar os dados.";

die("<h1>SERVER ERROR!!</h1>
$str[$i]
<br /><hr />
$_SERVER[SERVER_SIGNATURE]
");

} // fim error

// mostra erros padronizados
function error($i){
$str[0]="O Capacitor de Fluxo está com a carga baixa, não é possivel efetuar a transferencia de dados.";
$str[1]="O sistema de teletransporte está com defeito, o envio dos dados não foi efetuado";
$str[2]="Ocorreu uma microfissura no núcleo de plasma, a energia está sendo direcionada ao suporte de vida.";
$str[3]="Uma tempestade derrubou a torre da antena do satelite, a comunicação está inoperante.";
$str[4]="O nível do fluxo de dados está muito baixo, não é possível concluir a irrigação.";
$str[5]="O condutor de plasma está entupido, o dispositivo transdimensional está inoperante.";
$str[6]="A Policia Federal apreendeu o onibus que transportava as informações, não é possivel importar os dados.";
$str[7]="O navio que trazia o container de dados afundou devido a um ataque de piratas.";

die("<div
style='
background-image:url(images/erro.gif);
background-repeat:no-repeat;
display:block;
width:468px;
height:206px;
font-family:courier;
color:#ffff00;
font-size:13pt;
font-weight:bold;
margin-left:200px;
margin-top:80px;
padding-top:35px;
padding-left:25px;
padding-right:30px;
'>
<div style='display:block;margin-right:45px'>
ERRO:
$str[$i]
</div>
</div>
");

} // fim error





// mostra erros padronizados
function erro($i){

die("<div
style='
background-image:url(images/erro.gif);
background-repeat:no-repeat;
display:block;
width:468px;
height:206px;
font-family:courier;
color:#ffff00;
font-size:13pt;
font-weight:bold;
margin-left:200px;
margin-top:80px;
padding-top:35px;
padding-left:25px;
padding-right:30px;
'>
<div style='display:block;margin-right:45px'>
ERRO:
$i
</div>
</div>
");

} // fim error



// converte objetos em strings tranportaveis
function str_encode($s){
$s=serialize($s);
$s=gzcompress($s,9);
return base64_encode($s);
}

// restaura o objeto criado com a funcao str_encode()
function str_decode($s){
$s=base64_decode($s);
$s=gzuncompress($s);
$s=unserialize($s);
return $s;
}


// transforma array tri-dimensionais em arrays bi-dimenisonais
function array_simply($ar){
$_t=0;
foreach($ar as $d){

foreach($d as $v){
$temp[$_t]=$v;
}
$_t++;

}
return $temp;
} // fn


#developer functions
function pr($data) {
   echo '<pre>';
   print_r($data);
}

function pre($data) {
   pr($data);
   exit;
}

function vazios($qtd) {
   $brancos = '';
   for ($i=0; $i < $qtd; $i++) { 
      $brancos .= ' ';
   }
   return $brancos;
}  

function zeros($qtd) {
   $zeros = '';
   for ($i=0; $i < $qtd; $i++) { 
      $zeros .= '0';
   }
   return $zeros;
}

function minimo($string,$qtd, $retornaZeros = false) {
   $string = clean($string);
   if(mb_strlen($string) < $qtd) {
      if( $retornaZeros == true ) {
         $retorno = zeros($qtd - mb_strlen($string)).$string;
      } else {
         $retorno = $string.vazios($qtd - mb_strlen($string));
      }
   } else {
      $retorno = substr($string,0,$qtd);
   }
   return strtoupper($retorno);
}

// Limpa qualquer tipo de caractere de uma sequencia de numeros
function unmask($string) {
   #remove todos os caracteres não alfa-numéricos da string 
   $retorno = str_replace(array('R$',' ','.','-','_','(',')',',','/'), '', $string);
   return $retorno;
}

// Substitui todos os caracteres especiais pelas suas vogais respectivas
function clean($string) {

  $search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,ã,e,i,ø,õ,u");
  $replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,a,e,i,o,o,u");
  // $retorno = preg_replace('/[`^~\'"]/', null, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $string));
  $retorno = str_replace($search, $replace, $string);

  return $retorno;
}

function semAcentos($string) {
  
}


function modulo_11($num, $base=9, $r=0)  {
    /**
     *   Autor:
     *           Pablo Costa <pablo@users.sourceforge.net>
     *
     *   Função:
     *    Calculo do Modulo 11 para geracao do digito verificador 
     *    de boletos bancarios conforme documentos obtidos 
     *    da Febraban - www.febraban.org.br 
     *
     *   Entrada:
     *     $num: string numérica para a qual se deseja calcularo digito verificador;
     *     $base: valor maximo de multiplicacao [2-$base]
     *     $r: quando especificado um devolve somente o resto
     *
     *   Saída:
     *     Retorna o Digito verificador.
     *
     *   Observações:
     *     - Script desenvolvido sem nenhum reaproveitamento de código pré existente.
     *     - Assume-se que a verificação do formato das variáveis de entrada é feita antes da execução deste script.
     */                                        
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

function digitoVerificadorBradesco_nossonumero($numero) {
    $resto2 = modulo_11($numero, 7, 1);
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


?>