<?php
/*/dados de envio
$ddd = 13; #aqui � o ddd
$telefone = 91112497; #aqui � o numero que receber� a mesagem
$operadora = "clarotorpedo.com.br"; #aqui � o endereco da operadora que utilizaremos.

//enviando o e-mail
mail($ddd.$telefone."@".$operadora, "testinho", "puta que pariu");

*/



$p=600; // valor
$t=2.5; // taxa de correcao

$n=($p/100)*$t;
$n+=$p;

echo $n;





?>

