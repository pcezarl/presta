<?php
  
// cadastra cliente

include "lib/var.php";
include "lib/func.php";
include "lib/class_db.php";

$cnpj      = limpa($_POST["cnpj"]);
$info      = limpa($_POST["info"]);
$conta     = limpa($_POST["conta"]);
$banco     = limpa($_POST["banco"]);
$razao     = limpa($_POST["razao"]);
$agencia   = limpa($_POST["agencia"]);
$acessorio = limpa($_POST["acessorio"]);


// teste do form

if( (!$razao) || (!$cnpj) || (!$agencia) || (!$conta) || (!$banco) ) {

	die("
		<body style=\"color:white;background-color:#A21A24;font-family:verdana  \">
			<h2 style=\"display:inline \">OPPS!!</h2>:<b>FORMULARIO PREENCHIDO DE FORMA INCORRETA</B><br />
			<span style=\"font-size:8pt\">Alguns campos são obrigatorios</span>"
	);
}
// fim teste do form

$db = new db();

$data = date("Y-m-d");

$sql = "INSERT INTO contas (id, razao, cnpj, agencia, conta, data, info, banco, acessorio) values (NULL,'$razao','$cnpj','$agencia','$conta','$data','$info', '$banco', '$acessorio');";
$teste = $db->query($sql);

if($db->status=="ok") {
	$msg="Ok - Conta adicionada com sucesso";
	$cor="#CDFF9F";
}else{
	$msg="Opss - ocorreu um erro, a conta nao foi adicionada.";
	$cor="#FF9FA2";
}

?>
<xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt-br" lang="pt-br">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-language" content="pt-br" />

<script type="text/javascript" src="js/jquery.js" ></script>


<script type="text/javascript"> 
<!--

function pp(){
parent.$("#c_proc").fadeOut(1000);
parent.document.getElementById("myform").reset();
}

setTimeout("pp()",4000);

-->
</script>

</head>
<body>
<div style="display:block;width:100%;font-size:14pt  ;background:<?php echo $cor?>" id="okk">
<img src="images/alerta.gif" alt="" /><?php echo $msg ?>
</div>
</body>
</html>
