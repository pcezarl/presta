<form method="post">
<input type="text" name="dia">/<input type="text" name="mes">/<input type="text" name="ano">
<input type="submit" value="testar">

</form>

<hr>
<pre>
<?php

function ontem($d){
// by Valmirez
// formato de entrada dd/mm/YYYY

$st=strtotime($d);
$ontem=strtotime("-1 day",$st);
$ontem=date("d/m/Y",$ontem);
return $ontem;
} // fim da funcao

if($_POST["dia"]){

$dt="$_POST[dia]-$_POST[mes]-$_POST[ano]";

echo "antes: ".$dt."<br>";
echo "dpois: ".ontem($dt)  ;

}


?>