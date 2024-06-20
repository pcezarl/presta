<?php


// gera grafico com os edificios com mais aptos vendidos
// ordenado pela qdte de aptos vendidos


include "../lib/var.php";
include "../lib/class_db.php";
include "../lib/phplot.php";

$db=new db;

$sql="
select count(*) as qt_ap,ed_nome as edificio  from aptos join edificios on ap_ed=id_edificio
where ap_vendido='s' group by edificio order by qt_ap desc
";

$db->query($sql);
if(status=="erro")die($db->erro);

while($m=mysql_fetch_object($db->result)){

$dados[]=array($m->edificio,$m->qt_ap);
$leg[]=$m->edificio;
}

$gr=new PHPlot(780,500,'',"../images/fundo_chart.jpg");  

$gr->SetTTFPath('../font');
$gr->SetDefaultTTFont('arial.ttf');
$gr->SetUseTTF('arial.ttf');

$gr->SetFont('legend',  'arial.ttf', 15);
$gr->SetFont('x_label', 'arial.ttf', 9);
$gr->SetFont('x_title', 'arial.ttf', 15);
$gr->SetFont('y_title', 'arial.ttf', 18);
$gr->SetFont('y_label', 'arial.ttf', 11);

$gr->SetXDataLabelPos("plotdown");
$gr->SetYDataLabelPos('plotin');

$gr->SetTitle("vendas por edifício");
$gr->SetPlotType('bars');
$gr->SetFileFormat("png");
$gr->SetDataType('text-data');
$gr->SetDataColors(array('#1A285E', '#2D44A2', '#516ACE', '#9AA9E2','#BBC5EC','#D6DCF3'));
$gr->SetMarginsPixels(50,50,50,50);
$gr->SetDataValues($dados);
$gr->SetShading(15);
$gr->DrawGraph();
?>