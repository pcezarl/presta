<?php
  
////////////
// venda de apartamento
///////////


include "lib/var.php";
include "lib/func.php";

$i=limpa($_GET["i"]);

$p= new tpl("$modelo");
$pn=new tpl("tpl/tpl_form_venda_apto.html");
$db=new db;

// monta lista de clientes
$db->query("select id_cliente, cli_nome from clientes order by cli_nome");
$r=$db->result;

$pn->begin_loop("cli");
while($m=mysql_fetch_array($r)){
$d["id"]=$m["id_cliente"];
$d["nome"]=$m["cli_nome"];
$pn->set_loop($d);
}
$pn->end_loop("cli");
// fim lista de clientes

// monta dados do apto
$db->query("select * from aptos join edificios on ap_ed=id_edificio where id_apto='$i'");
if($db->rows==0)die("ACESSO NEGADO: A INTERFACE NÃO FOI ESTABILIZADA");
if($db->status=="erro")die("ERRO SQL:{$db->erro}");

$m=mysql_fetch_object($db->result);
if($m->ap_vendido=='s')die("ACESSO NEGADO: ESTE APARTAMENTO JA FOI VENDIDO");

$pn->set("ed",$m->ed_nome);
$pn->set("ap",$m->ap_num);
$pn->set("id_apto",$m->id_apto);
$pn->set("valor",mil($m->ap_valor));

// fim dados apto

$p->set("ap",$m->ap_num);
$p->set("ed",$m->ed_nome);
$p->set("conteudo",$pn->tvar());
$p->set("pagina","Venda de apartamento");
$p->tprint();
?>
