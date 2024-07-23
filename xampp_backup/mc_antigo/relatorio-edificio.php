<?php
///// processa o relatorio
/// isso vai ser um saco pra fazer


include "lib/var.php";
include "lib/func.php";
$db=new db();

foreach($_GET as $n=>$v){
$$n=$v;
}

$inicio_normal=mydata($inicio);
$final_normal=mydata($final);


$db->query("select ed_nome from edificios where id_edificio='$i'");
$nome_edificio=$db->get_val('ed_nome');

$db->reset();

///// vai comecar

?>

 <?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt-br" lang="pt-br">
<head>
<meta name="expires" CONTENT= "Mon, 18 Mar 2115 00:00:00 GMT">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="content-language" content="pt">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<meta name="generator" content="PhpED Version 5.9 (Build 5921)">
<link rel="stylesheet" type="text/css" href="style.css" />
<title></title>

</head>
<body  style="background-image:url('');background-color: white;">
<h1 class="titulo">Dados de <?php echo $inicio_normal?> até <?php echo $final_normal?> do edifico <?php echo $nome_edificio ?> <a href='<?php print(basename($_SERVER[HTTP_REFERER])); ?>'  class='opt' style="margin-left: 30px;">Voltar aos edificios</a></h1>
<div class="info">Clique no numero do apartamento para obter mais detalhes</div>
<br />
<h2 class="titulo">Valor a receber</h2>
<table width="80%" align="center" style="border-collapse:collapse;font-family: monospace;">
<tr style="background-color: #102149;color:white">
<th>Apto</th>
<th>Valor a receber</th>
<th>total de prestações</th>

</tr>
<?php
$sql="
select sum(pr_valor) as total, count(id_presta) as parc, ap_num, id_apto as id from prestacoes

left join aptos on pr_apto=id_apto
left join edificios on ap_ed=id_edificio

where pr_vencimento between '$inicio' and '$final' and pr_pago='n' and id_edificio='$i'
group by ap_num

";

//die($sql);

$db->query($sql);

while($d=mysql_fetch_object($db->result)){/// loop

$c++;
$cor=($c%2)?"corsim":"cornao";

$total_valor+=$d->total;
$total_parc+= $d->parc;

$valor=mil($d->total);
echo "
<tr class='$cor'>
<td><a href='apto.php?i={$d->id}' class='link-lista' target='_parent'>{$d->ap_num}</a></td>
<td style='border-left:1px solid #585858;text-align:right'>{$valor}</td>
<td style='border-left:1px solid #585858;text-align:right'>{$d->parc}</td>
</tr>
";

}// fim lloop

$total_valor=mil($total_valor);
echo "
<tr style='background:#F9C700;color:black;font-weight:bold'>
<td>TOTAIS</td>
<td style='border-left:1px solid #585858;text-align:right'>{$total_valor}</td>
<td style='border-left:1px solid #585858;text-align:right'>{$total_parc}</td>
</tr>
";

?>
</table><br /><br />
<!-- /////////////////// -->


<h2 class="titulo">Valor recebido</h2>
<table width="80%" align="center" style="border-collapse:collapse;font-family: monospace;">
<tr style="background-color: #102149;color:white">
<th>Apto</th>
<th>Valor recebido</th>
<th>total de prestações</th>

</tr>
<?php
$sql="
select sum(pr_valor) as total, count(id_presta) as parc, ap_num, id_apto as id from prestacoes

left join aptos on pr_apto=id_apto
left join edificios on ap_ed=id_edificio

where pr_vencimento between '$inicio' and '$final' and pr_pago='s' and id_edificio='$i'
group by ap_num

";

$db->query($sql);
$c=0;
$total_valor=0;
$total_parc=0;


while($d=mysql_fetch_object($db->result)){/// loop

$c++;
$cor=($c%2)?"corsim":"cornao";

$total_valor+=$d->total;
$total_parc+=$d->parc;

$valor=mil($d->total);
echo "
<tr class='$cor'>
<td>{$d->ap_num}</td>
<td style='border-left:1px solid #585858;text-align:right'>{$valor}</td>
<td style='border-left:1px solid #585858;text-align:right'>{$d->parc}</td>
</tr>
";

}// fim lloop

$total_valor=mil($total_valor);
echo "
<tr style='background:#F9C700;color:black;font-weight:bold'>
<td>TOTAIS</td>
<td style='border-left:1px solid #585858;text-align:right'>{$total_valor}</td>
<td style='border-left:1px solid #585858;text-align:right'>{$total_parc}</td>
</tr>
";

?>
</table><br /><br />
<!-- /////////////////// -->


</body>
</html>
