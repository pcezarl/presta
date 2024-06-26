<?php
  
/////// visualiza boleto emitido

include "lib/var.php";
include "lib/func.php";


$i=limpa($_GET["i"]);

$db=new db;

$sql="select * from boletos
left join clientes   on bo_prop     =id_cliente
left join aptos      on id_apto     =bo_apto
left join edificios  on id_edificio =ap_ed
left join prestacoes on bo_presta   =id_presta
where bo_presta='$i'
";
$db->query($sql);
if($db->status=="erro")die($db->erro);
$d=$db->data_object;

$b= new boleto_HSBC();
$b->imprimir=false;


$b->init();
$b->set("demonstrativo1","parcela {$d->bo_num_presta}");
$b->set("demonstrativo2","Edificio {$d->ed_nome} apto: {$d->ap_num}");
$b->set("demonstrativo3","Taxa do boleto: R\$ ".mil($b->taxa_boleto));
$b->set("demonstrativo4","Construtura Aires - construindo seu sonho da casa propria");
$b->set("endereco1","{$d->cli_rua},{$d->cli_numero} - {$d->cli_bairro}");
$b->set("endereco2","{$d->cli_cidade} - {$d->cli_estado} -CEP:{$d->cli_cep}");
$b->val("valor_boleto",$d->bo_valor);
$b->val("data_vencimento",mydata($d->bo_data_vence));
$b->val("numero_documento",$d->bo_ndoc);
$b->set("data_documento",date("d/m/Y"));
$b->set("sacado",$d->cli_nome);
$b->draw();
$b->fim();
$boleto=$b->layout;





////////////  converte em zip

$boleto=str_replace("imagens/","",$boleto);

//error_reporting(E_ALL);


include "teste/mht.lib.php";
$MhtFileMaker = new MhtFileMaker();


$MhtFileMaker->SetFrom('Construtora Aires <aires@aires.com.br>');
$MhtFileMaker->SetSubject('Boleto');
$MhtFileMaker->AddContents("","text/html",$boleto);

$MhtFileMaker->AddFile('imagens/logohsbc.jpg', 'logohsbc.jpg', NULL);
$MhtFileMaker->AddFile('imagens/1.png', '1.png', NULL);
$MhtFileMaker->AddFile('imagens/2.png', '2.png', NULL);
$MhtFileMaker->AddFile('imagens/3.png', '3.png', NULL);
$MhtFileMaker->AddFile('imagens/6.png', '6.png', NULL);
$MhtFileMaker->AddFile('imagens/b.png', 'b.png', NULL);
$MhtFileMaker->AddFile('imagens/p.png', 'p.png', NULL);
$MhtFileMaker->AddFile('imagens/logo_empresa.png', 'logo_empresa.png', NULL);


$boleto= $MhtFileMaker->GetFile();
 
$zip = new ZipArchive();
$filename = "boleto.zip";
if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
    exit("cannot open <$filename>\n");
}

$zip->addFromString("boleto.mht", "$boleto");
$zip->close();

require_once "teste/dSendMail2.inc.php";

$valor=mil($d->bo_valor);
$msg="
Segue em anexo<br />
boleto referente ao pagamento de parcela do apartamento {$d->ap_num} do edificio {$d->ed_nome}<br>
Valor da prestação: R\${$valor}<br />
Numero do documento: {$d->bo_ndoc}<br /><br /><br />
caso tenha problemas na visualização do boleto, utilize a linha digitavel:<br /><br />
<span style='padding:3px;background:#C0C0C0;border:1px solid;#585858;font-family:courier;font-size:11pt;color:black;font-weight:bold'>{$b->dadosboleto[linha_digitavel]} </span>

";

//die($msg);

$m = new dSendMail2;
$m->setSubject("boleto");
$m->setFrom("nobody@nowhere.com");   
$m->setTo  ("vendas@airesimoveis.com.br"); 
$m->setMessage($msg);
$m->autoAttachFile("boleto.zip", file_get_contents("boleto.zip"));
$m->send();

@unlink("boleto.zip");





?>
