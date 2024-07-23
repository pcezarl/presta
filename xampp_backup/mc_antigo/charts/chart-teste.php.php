<?php
// gera grafico com as prestacoes do mess atual  
// separa as pagas das nao-pagas


header ("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");

include "../lib/var.php";
include "../lib/class_db.php";
include "../lib/phplot.php";
include "../lib/func.php"; 

$db=new db;
$gr=new PHPlot(700,320);
$gr->SetPrintImage(0);

$i=$_GET["i"];

$i=4;


// parte 1
$sql="
select count(*) as qt,pr_pago from prestacoes
where pr_apto='$i'
group by pr_pago
";

$gr->SetPlotAreaPixels(0,0,350,300);

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
//$gr->SetMarginsPixels(10,0,20,0);
$gr->SetDataValues($dados);
$gr->SetShading(0);
$gr->SetLegendPixels(0, 15);
//foreach ($dados as $row)$gr->SetLegend(implode(': ', $row));
$gr->DrawGraph();



// parte 2
unset($dados);
//unset($dados); 
$sql="
select sum(pr_valor) as qt,pr_pago from prestacoes
where pr_apto='$i'
group by pr_pago
";

$gr->SetPlotAreaPixels(350,0,700,300);

$db->query($sql);
if(status=="erro")die($db->erro);

while($m=mysql_fetch_object($db->result)){
$pago=($m->pr_pago=='s')?"Pago":"Não-pago";
$dados[]=array($pago,$m->qt);
}

$gr->SetPlotType('pie');
$gr->SetFileFormat("png");
$gr->SetDataType('text-data-single');
$gr->SetDataColors(array('#D8CD01', '#936000'));
$gr->SetDataValues($dados);
$gr->SetShading(0);
$gr->SetLegendPixels(0, 15);
//foreach ($dados as $row)$gr->SetLegend(implode(': ', $row));
$gr->DrawGraph();




// finaliza

$gr->PrintImage();


?>