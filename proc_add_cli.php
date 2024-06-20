<?php
  
// cadastra cliente

include "lib/var.php";
include "lib/func.php";
include "lib/class_db.php";

$nome=   limpa($_POST["nome"]);
$email=  limpa($_POST["email"]);
$cpf=    limpa($_POST["cpf"]);
$tel=    limpa($_POST["telefone"]);
$rua=    limpa($_POST["rua"]);
$bairro= limpa($_POST["bairro"]);
$cidade= limpa($_POST["cidade"]);
$cep=    limpa($_POST["cep"]);
$estado= limpa($_POST["estado"]);
$info=   limpa($_POST["info"]);
$numero= limpa($_POST["numero"]);

// teste do form

if( 
(!$nome)||
(!$cpf)||
(!$tel)||
(!$rua)||
(!$bairro)||
(!$cidade)||
(!$estado)||
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

$sql="insert into clientes
(cli_nome,cli_email,cli_cpf,cli_tel,cli_rua,cli_numero,cli_bairro,cli_cidade,cli_cep,cli_estado,cli_obs,cli_data_cadastro)
values
('$nome','$email','$cpf','$tel','$rua','$numero','$bairro','$cidade','$cep','$estado','$info','$data')
";


$db->query($sql);

if($db->status=="ok"){
$msg="OK!! cliente adicionado com sucesso";
$cor="#CDFF9F";

}else{
$msg="OPPs!! ocorreu um erro alienigena, o cliente nao foi adicionado.";
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
