<?php


// gera grafico com a arrecadaчуo mensal listada por ano
// ordem cornologica(sic)


include "../lib/var.php";
include "../lib/class_db.php";
include "../lib/phplot.php";
include "../lib/func.php";

$db=new db;

$ano=date("Y");

$sql="
select sum(pr_valor)as valor,month(pr_vencimento)as mes
from prestacoes
where year(pr_vencimento)='2010' and pr_pago='n'
group by mes
";

$db->query($sql);
if(status=="erro")die($db->erro);


while($m=mysql_fetch_object($db->result)){




$dados[]=array(
$mes_extenso_abrev[$m->mes],
$m->valor
);



$leg[]=mil($m->valor);

}



$gr=new PHPlot(780,500,'',"../images/fundo_chart.jpg");  

$gr->SetTTFPath('../font');
$gr->SetDefaultTTFont('arial.ttf');
$gr->SetUseTTF('arial.ttf');

$gr->SetFont('legend',  'arial.ttf', 10);
$gr->SetFont('x_label', 'arial.ttf', 9);
$gr->SetFont('x_title', 'arial.ttf', 10);
$gr->SetFont('y_title', 'arial.ttf', 18);
$gr->SetFont('y_label', 'arial.ttf', 8);

$gr->SetXDataLabelPos("plotdown");
$gr->SetYDataLabelPos('plotin');

//$gr->SetLegend($leg);


$gr->SetTitle("previsуo de arrecadaчуo mensal durante o ano de $ano");
$gr->SetPlotType('bars');

$gr->SetFileFormat("png");
$gr->SetDataType('text-data');
$gr->SetDataColors(array('#FFFFFF'));

$gr->SetMarginsPixels(50,50,50,50);


$gr->SetDataValues($dados);
$gr->SetShading(0);
$gr->DrawGraph();
?>