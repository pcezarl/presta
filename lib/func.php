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
$s=preg_replace("/[<>#%\^]/","",$s);
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
$str[0]="O Capacitor de Fluxo est� com a carga baixa, não � possivel efetuar a transferencia de dados.";
$str[1]="O sistema de teletransporte est� com defeito, o envio dos dados não foi efetuado";
$str[2]="Ocorreu uma microfissura no n�cleo de plasma, a energia est� sendo direcionada ao suporte de vida.";
$str[3]="Uma tempestade derrubou a torre da antena do satelite, a comunica��o est� inoperante.";
$str[4]="O n�vel do fluxo de dados est� muito baixo, não � poss�vel concluir a irriga��o.";
$str[5]="O condutor de plasma est� entupido, o dispositivo transdimensional est� inoperante.";
$str[6]="A Policia Federal apreendeu o onibus que transportava as informa��es, não � possivel importar os dados.";

die("<h1>SERVER ERROR!!</h1>
$str[$i]
<br /><hr />
$_SERVER[SERVER_SIGNATURE]
");

} // fim error

// mostra erros padronizados
function error($i){
$str[0]="O Capacitor de Fluxo est� com a carga baixa, não � possivel efetuar a transferencia de dados.";
$str[1]="O sistema de teletransporte est� com defeito, o envio dos dados não foi efetuado";
$str[2]="Ocorreu uma microfissura no n�cleo de plasma, a energia est� sendo direcionada ao suporte de vida.";
$str[3]="Uma tempestade derrubou a torre da antena do satelite, a comunica��o est� inoperante.";
$str[4]="O n�vel do fluxo de dados est� muito baixo, não � poss�vel concluir a irriga��o.";
$str[5]="O condutor de plasma est� entupido, o dispositivo transdimensional est� inoperante.";
$str[6]="A Policia Federal apreendeu o onibus que transportava as informa��es, não � possivel importar os dados.";
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

function minimo($string, $qtd, $retornaZeros = false) {
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
   #remove todos os caracteres não alfa-numericos da string
   $retorno = str_replace(array('R$',' ','.','-','_','(',')',',','/'), '', $string);
   return $retorno;
}

   // Substitui todos os caracteres especiais pelas suas vogais respectivas
	function clean($string) {
      $pattern = array("'é'", "'è'", "'ë'", "'ê'", "'É'", "'È'", "'Ë'", "'Ê'", "'Ã'", "'ã'", "'á'", "'à'", "'ä'", "'â'", "'å'", "'Á'", "'À'", "'Ä'", "'Â'", "'Å'", "'ó'", "'ò'", "'ö'", "'ô'", "'Ó'", "'Ò'", "'Ö'", "'Ô'", "'í'", "'ì'", "'ï'", "'î'", "'Í'", "'Ì'", "'Ï'", "'Î'", "'ú'", "'ù'", "'ü'", "'û'", "'Ú'", "'Ù'", "'Ü'", "'Û'", "'ý'", "'ÿ'", "'Ý'", "'ø'", "'Ø'", "'œ'", "'Œ'", "'Æ'", "'ç'", "'Ç'");
		$replace = array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'i', 'i', 'i', 'I', 'I', 'I', 'I', 'I', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'y', 'y', 'Y', 'o', 'O', 'a', 'A', 'A', 'c', 'C'); 

		$retorno = preg_replace($pattern, $replace, $string);

      return $retorno;
   }

function modulo_11($num, $base=9, $r=0)  {
    /**
     *   Autor:
     *           Pablo Costa <pablo@users.sourceforge.net>
     *
     *   Fun��o:
     *    Calculo do Modulo 11 para geracao do digito verificador 
     *    de boletos bancarios conforme documentos obtidos 
     *    da Febraban - www.febraban.org.br 
     *
     *   Entrada:
     *     $num: string num�rica para a qual se deseja calcularo digito verificador;
     *     $base: valor maximo de multiplicacao [2-$base]
     *     $r: quando especificado um devolve somente o resto
     *
     *   Sa�da:
     *     Retorna o Digito verificador.
     *
     *   Observa��es:
     *     - Script desenvolvido sem nenhum reaproveitamento de c�digo pr� existente.
     *     - Assume-se que a verifica��o do formato das vari�veis de entrada � feita antes da execu��o deste script.
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