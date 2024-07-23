<?php
  
////////////
// listagem dos clientes
///////////

include "lib/var.php";
include "lib/func.php";
include "lib/class_tpl.php";
include "lib/class_db.php";

$pg = new tpl($modelo);
$pn = new tpl("tpl/tpl_lista_cli.html");
$db = new db;

$db->query("select * from clientes order by cli_nome");
if($db->status=="erro")die($db->erro);
if($db->rows==0)error(5);

$cont="<h2 class='titulo'>Listagem de clientes</h2>";

$pn->begin_loop("linha");

while($dados=mysql_fetch_object($db->result)){
$c++;$cor=($c%2==0)?"corsim":"cornao";


$dd["id"]=$dados->id_cliente;
$dd["nome"]=$dados->cli_nome;
$dd["email"]=$dados->cli_email;
$dd["cor"]=$cor;
$dd["telefone"]=$dados->cli_tel;



$pn->set_loop($dd);
}

$pn->end_loop("linha");


// monta pagina
$cont.=$pn->tvar();
$pg->set("conteudo",$cont);
$pg->set("pagina","Lista de Clientes");
$pg->tprint();
?>