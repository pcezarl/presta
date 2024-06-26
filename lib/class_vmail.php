<?php
  
/////////// class vmail

class vmail{

var $conf;

function __construct(){
$this->conf= array(
"de"=>"construtora@aires.com.br",
"responder"=>"construtora@aires.com.br"
);


} //////////


function para($t)     {$this->conf["para"]    =$t;} //// configura destinatario
function assunto($t)  {$this->conf["assunto"] =$t;} //// configura assunto
function mensagem($t) {$this->conf["mensagem"]=$t;} //// configura mensagem

function anexa($f){
$this->conf["nome_anexo"]=$f;
$f=file_get_contents($f)or die("erro ao codificar o arquivo para anexar");
$this->conf["anexo"]=chunk_split(base64_encode($f)); 
}

function envia(){

$mailheaders = "From: {$this->conf["de"]}\n";
$mailheaders.= "Reply-To: {$this->conf["responder"]}\n";
$mailheaders.= "X-Mailer: Microsoft Outlook Express 6.00.2900.5512\n";
$mailheaders.= "X-Spam-Status:No, tests=filter, spamicity=0.003130\n";
$mailheaders.= "X-Scripter:Valmirez System\n";


$msg_body = stripslashes($this->conf["mensagem"]);
$encoded_attach = $this->conf["anexo"];
$mailheaders .= "MIME-version: 1.0\n";
$mailheaders .= "Content-type: multipart/mixed; ";
$mailheaders .= "boundary=\"Message-Boundary\"\n";
$mailheaders .= "Content-transfer-encoding: 7BIT\n";
$mailheaders .= "X-attachments: {$this->conf["nome_anexo"]}";
$body_top = "--Message-Boundary\n";
$body_top .= "Content-type: text/plain; charset=US-ASCII\n";
$body_top .= "Content-transfer-encoding: 7BIT\n";
$body_top .= "Content-description: Mail message body\n\n";
$msg_body = $body_top . $msg_body;
$msg_body .= "\n\n--Message-Boundary\n";
$msg_body .= "Content-type: application/octet-stream; name=\"{$this->conf["nome_anexo"]}\"\n";
$msg_body .= "Content-Transfer-Encoding: BASE64\n";
$msg_body .= "Content-disposition: attachment; filename=\"{$this->conf["nome_anexo"]}\"\n\n";
$msg_body .= "{$this->conf["anexo"]}\n";
$msg_body .= "--Message-Boundary--\n"; 

mail($this->conf["para"], stripslashes($this->conf["assunto"]), $msg_body, $mailheaders); 



}// fim envia

function reset(){
unset($this->conf);
self::__construct();

}



}//// fim classe



?>
