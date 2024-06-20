<?php
  
// cadastra cliente

include "lib/var.php";
include "lib/func.php";
include "lib/class_db.php";

$nome=   limpa($_POST["nome"]);
$info=   limpa($_POST["obs"]);
$end =   limpa($_POST["endereco"]);

if( 
(!$nome)||
(!$end)
){

die("
<body bgcolor='#A21A24' text='white'>
<b>FORMULARIO PREENCHIDO DE FORMA INCORRETA</B>
"
);
}

$db=new db();
$sql="insert into edificios(ed_nome,ed_end,ed_info) values('$nome','$end','$info')";

$db->query($sql);
if($db->status=="ok"){
$msg="OK!! edificio adicionado com sucesso";
$cor="#CDFF9F";

}else{
$msg="OPPs!! ocorreu um erro alienigena, o edificio nao foi adicionado.";
$cor="#FF9FA2";
}

?>
<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt-br" lang="pt-br">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-language" content="pt-br" />
<title>Cobrança Aires :: {{pagina}}</title>

<script type="text/javascript" src="js/jquery.js" ></script>
<script type="text/javascript" src="js/jquery.delay.js"></script>

<script type="text/javascript"> 
<!--


$(document).ready(function(){

$("#okk").delay(2000).fadeOut(300);
parent.document.getElementById("form").reset();

}); //fim on ready

-->
</script>

</head>
<body>
<div style="display:block;width:100%;font-size:14pt  ;background:<?php echo $cor?>" id="okk">
<img src="images/alerta.gif" alt="" /><?php echo $msg ?>
</div>
</body>
</html>
