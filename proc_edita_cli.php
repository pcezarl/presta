<?php
  
// edicao de cliente


include "lib/var.php";
include "lib/func.php";
include "lib/class_db.php";

$d=new db;

foreach($_POST as $n=>$v ){
$$n=limpa($v);
} 
$sql="update clientes set
cli_nome='$nome',
cli_email='$email',
cli_tel='$telefone',
cli_cpf='$cpf',
cli_rua='$rua',
cli_numero='$numero',
cli_bairro='$bairro',
cli_cidade='$cidade',
cli_cep='$cep',
cli_estado='$estado'


where id_cliente='$id'";

$d->query($sql);

if($d->status=="erro")die($d->erro);


//echo $d->status;

header("location:cliente.php?i=$id");
?>
