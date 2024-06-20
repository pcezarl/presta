<?php

/////// visualiza boleto emitido

include "lib/var.php";
include "lib/func.php";
//include "lib/class_HSBC.php";

$i=limpa($_GET["i"]);

$db=new db; // banco principal
//$d1=new db; // banco temporario

$sql="select * from boletos
left join clientes   on bo_prop     =id_cliente
left join aptos      on id_apto     =bo_apto
left join edificios  on id_edificio =ap_ed
left join prestacoes on bo_presta   =id_presta
where bo_presta='$i'
";
$db->query($sql);
if($db->status=="erro")die($db->erro);
$d=$db->data_object;

$b= new boleto_HSBC();

$tipo=$tipo_parcela[$d->pr_tipo];

$db->query("select max(pr_num) as idx from prestacoes where pr_apto='{$d->pr_apto}' and pr_tipo='{$d->pr_tipo}' ");
$idx=$db->get_val("idx");

$b->set("demonstrativo1","parcela {$d->bo_num_presta} de $idx  ($tipo) ");
$b->set("demonstrativo2","Edificio {$d->ed_nome} apto: {$d->ap_num}");
//$b->set("demonstrativo4","taxa do boleto: R\$ ".mil($b->taxa_boleto));
//$b->set("demonstrativo3","");
$b->set("endereco1","{$d->cli_rua},{$d->cli_numero} - {$d->cli_bairro}");
$b->set("endereco2","{$d->cli_cidade} - {$d->cli_estado} -CEP:{$d->cli_cep}");
$b->val("valor_boleto",$d->bo_valor);
$b->val("data_vencimento",mydata($d->bo_data_vence));
$b->val("numero_documento",$d->bo_ndoc);
$b->set("data_documento",date("d/m/Y"));
$b->set("sacado",$d->cli_nome);
$b->draw();
print($b->layout);

?>
