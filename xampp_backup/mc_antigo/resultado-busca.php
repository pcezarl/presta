<?php
  
////////////
// resultado da busca
///////////

include "lib/var.php";
include "lib/func.php";
include "lib/class_tpl.php";
include "lib/class_db.php";

$palavra=limpa($_POST["palavra"]);
$campo=limpa($_POST["campo"]);


$lista_campos=array(
"cliente"=>"cli_nome",
"edificio"=>"ed_nome"
);

$lista_tabelas=array(
"cliente"=>"clientes",
"edificio"=>"edificios"
);

$sql="select * from $lista_tabelas[$campo] where $lista_campos[$campo] like '%$palavra%'";

$p  = new tpl("$modelo");
$db = new db;

// monta lista de clientes
$db->query($sql);

if($db->status=="erro")die($db->erro);

$r=$db->result;
$qt=$db->rows;

$cont="
<h2 class='titulo'>resultado da busca: $qt</h2>
";

// busca de cliente
if($campo=="cliente"){

// loop de dados
while($dados=mysql_fetch_object($r)){
$c++;
$cor=(is_int($c/2))?"corsim":"cornao";

$cont.="<div class='$cor'><a href='cliente.php?i={$dados->id_cliente}' class='listagem'>{$dados->cli_nome}</a></div>\n\t";
}// fim loop
}
// fim busca cli

// busca de edificio
if($campo=="edificio"){

// loop de dados
while($dados=mysql_fetch_object($r)){
$c++;
$cor=(is_int($c/2))?"corsim":"cornao";

$cont.="<div class='$cor'><a href='edificio.php?i={$dados->id_edificio}' class='listagem'>{$dados->ed_nome}</a></div>\n\t";
}// fim loop
}
// fim busca edificio


$p->set("conteudo",$cont);
$p->set("pagina","Resultado da busca");
$p->tprint();
?>