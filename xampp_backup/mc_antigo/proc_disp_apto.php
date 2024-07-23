<?php
// marca o apto como entregue


include "lib/var.php";
include "lib/func.php";
include "lib/class_db.php";

$i=limpa($_GET["i"]);

$db = new db;
$db->query("update aptos set ap_entregue='s' where id_apto='$i'");


if($db->status=='erro')die($db->erro);

header("location:apto.php?i=$i");


?>
