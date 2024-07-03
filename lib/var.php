<?php /// Valmirez System - valmirez@hotmail.com

error_reporting(164);
// error_reporting(-1);

// phpinfo();
ini_set("default_charset", 'utf-8');

ob_start("ob_gzhandler");

function __autoload($c) {require_once "lib/class_".$c . '.php';}
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
$_version=PHP_VERSION;
die("
<h1>ERRO DE SISTEMA</h1>
A versãoo do compilador não é compativel com o sistema.<br />
Versão atual: <b>$_version</b><br />
Versão requerida: <b>5.3.0</b> ou posterior.<br />
<hr>
$_SERVER[SERVER_SIGNATURE]
");
unset($_version);
}

// para o autobase
$_host="http://127.0.0.1/mc";

$mysql["host"]="127.0.0.1";
$mysql["dados"]="teste";
$mysql["user"]="root";
$mysql["pass"]="zaq1xsw2";

$mes_extenso=array(
"",
"Janeiro",
"Fevereiro",
"Março",
"Abril",
"Maio",
"Junho",
"Julho",
"Agosto",
"Setembro",
"Outubro",
"Novembro",
"Dezembro"
);

$mes_extenso_abrev=array(
"",
"Jan",
"Fev",
"Mar",
"Abr",
"Mai",
"Jun",
"Jul",
"Ago",
"Set",
"Out",
"Nov",
"Dez"
);

$modelo="modelo.html";


// lista de tipos de parcela
$tipo_parcela=array(
"n"=>"mensalidade",
"e"=>"entrada",
"s"=>"semestral",
"t"=>"trimestral",
"c"=>"chaves",
"a"=>"avulsa",
"i"=>"intermediaria"
);
?>