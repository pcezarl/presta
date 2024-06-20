<?php

// marca a prestacao como paga

include "lib/var.php";
include "lib/func.php";
include "lib/class_db.php";

$i=limpa($_GET["i"]);
$a=limpa($_GET["a"]);

if($a=="direct"){
$rd="index.php";
}else{
$rd=basename($_SERVER['HTTP_REFERER']);
}

$db= new db;

$data=date("Y-m-d");
$sql="update prestacoes set pr_pago='s', pr_data_pago='$data' where id_presta='$i'";
$db->query($sql);
if($db->status=="erro")die($db->erro);


?>
<script type="text/javascript">
parent.location="<?php echo $rd?>";
</script>
