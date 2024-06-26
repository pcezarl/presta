<?php
/*/dados de envio
$ddd = 13; #aqui é o ddd
$telefone = 91112497; #aqui é o numero que receberá a mesagem
$operadora = "clarotorpedo.com.br"; #aqui é o endereco da operadora que utilizaremos.

//enviando o e-mail
mail($ddd.$telefone."@".$operadora, "testinho", "puta que pariu");

*/



$p=600; // valor
$t=2.5; // taxa de correcao

$n=($p/100)*$t;
$n+=$p;

echo $n;





?>

