<?php
// gera grafico com as prestacoes do mes atual em um determinado ida de vencimento 
// separa as pagas das nao-pagas


include "../lib/var.php";
include "../lib/class_db.php";
include "../lib/phplot.php";

$db=new db;
$gr=new PHPlot(300,300);
$gr->SetBackgroundColor('white');
$gr->SetTransparentColor('white');

$ano=date("Y");
$mes=date("m");
$dia=$_GET["i"];

$data="$ano-$mes-$dia";


$sql="
select count(*) as qt,pr_pago from prestacoes
where pr_vencimento='$data'
group by pr_pago
";

$db->query($sql);
if(status=="erro")die($db->erro);

while($m=mysql_fetch_object($db->result)){
$pago=($m->pr_pago=='s')?"Pago":"Não-pago";
$dados[]=array($pago,$m->qt);
}

$gr->SetPlotType('pie');
$gr->SetFileFormat("png");
$gr->SetDataType('text-data-single');
$gr->SetDataColors(array('#F0BA00', '#FFFB64', '#D8CD01', '#936000'));
$gr->SetMarginsPixels(50,50,50,50);
$gr->SetDataValues($dados);
$gr->SetShading(0);
$gr->SetLegendPixels(0, 15);

foreach ($dados as $row)$gr->SetLegend(implode(': ', $row));

$gr->DrawGraph();
?>