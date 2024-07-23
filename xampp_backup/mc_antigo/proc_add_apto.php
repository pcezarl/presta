<?php
  
// cadastra cliente

include "lib/var.php";
include "lib/func.php";
include "lib/class_db.php";

$edificio= limpa($_POST["edificio"]);
$numero=   limpa($_POST["numero"]);
$info=     limpa($_POST["info"]);
$valor=    limpa($_POST["valor"]);
$valor=    nformat($valor);

// teste do form

if( 
(!$valor)||
(!$edificio)||
(!$numero)
){

die("
<body style=\"color:white;background-color:#A21A24;font-family:verdana  \">
<h2 style=\"display:inline \">OPPS!!</h2>:<b>FORMULARIO PREENCHIDO DE FORMA INCORRETA</B><br />
<span style=\"font-size:8pt\">Alguns campos são obrigatorios</span>
"
);
}


// fim teste do form


$db=new db();

$data=date("Y-m-d");

$sql="insert into aptos
(ap_ed,ap_num,ap_valor,ap_vendido,ap_obs)
values
('$edificio','$numero','$valor','n','$info')
";


$db->query($sql);
$e=$db->erro;
if($db->status=="ok"){
$msg="OK!! apartamento adicionado com sucesso";
$cor="#CDFF9F";

}else{

$msg="OPPs!! ocorreu um erro alienigena, o apartamento nao foi adicionado.";
$cor="#FF9FA2";
}

?>
<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt-br" lang="pt-br">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-language" content="pt-br" />

<script type="text/javascript" src="js/jquery.js" ></script>
<script type="text/javascript" src="js/jquery.delay.js"></script>

<script type="text/javascript"> 
<!--

function pof(){
parent.document.getElementById("myform").reset();
parent.$("#c_proc").fadeOut(1000);
}

setTimeout("pof()",5000);
-->
</script>

</head>
<body>
<div style="display:block;width:100%;font-size:14pt  ;background:<?php echo $cor?>" id="okk">
<img src="images/alerta.gif" alt="" /><?php echo $msg ?>
</div>
</body>
</html>
