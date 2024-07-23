<?php
///// processa o relatorio
/// isso vai ser um saco pra fazer


include "lib/var.php";
include "lib/func.php";
$db=new db();

foreach($_GET as $n=>$v){
$$n=(integer)limpa($v);
}

$inicio_normal="$dia_inicio/$mes_extenso_abrev[$mes_inicio]/$ano_inicio";
$final_normal="$dia_final/$mes_extenso_abrev[$mes_final]/$ano_final";

$inicio="$ano_inicio-$mes_inicio-$dia_inicio";
$final="$ano_final-$mes_final-$dia_final";



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
<h1 class="titulo">Dados de <?php echo $inicio_normal?> até <?php echo $final_normal?></h1>
<div class="info">Clique no nome do edificio para obter mais detalhes</div>
<br />

<h2 class="titulo">Valor a receber</h2>
<table width="80%" align="center" style="border-collapse:collapse;font-family: monospace;">
<tr style="background-color: #102149;color:white">
<th>Edificio</th>
<th>Valor a receber</th>
<th>total de prestações</th>

</tr>
<?php
$sql="
select sum(pr_valor) as total, ed_nome, count(id_presta) as parc,id_edificio as id from prestacoes
left join aptos on pr_apto=id_apto
left join edificios on ap_ed=id_edificio
where pr_vencimento between '$inicio' and '$final' and pr_pago='n'
group by ed_nome
";

$db->query($sql);

while($d=mysql_fetch_object($db->result)){/// loop

$c++;
$cor=($c%2)?"corsim":"cornao";

$total_valor+=$d->total;
$total_parc+=$d->parc;

$valor=mil($d->total);
echo "
<tr class='$cor'>
<td><a href='relatorio-edificio.php?i={$d->id}&amp;inicio=$inicio&amp;final=$final' class='link-lista'>{$d->ed_nome}</a></td>
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
<th>Edificio</th>
<th>Valor recebido</th>
<th>total de prestações</th>

</tr>
<?php
$sql="
select sum(pr_valor) as total, ed_nome, count(id_presta) as parc, id_edificio as id from prestacoes
left join aptos on pr_apto=id_apto
left join edificios on ap_ed=id_edificio
where pr_vencimento between '$inicio' and '$final' and pr_pago='s'
group by ed_nome
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
<td>{$d->ed_nome}</td>
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
