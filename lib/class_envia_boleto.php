<?php

//////////////////
class envia_boleto{


var $boleto;

function __construct(){

$this->mht =new mht();
$this->m   =new vmail();

} //// fim construct



function set_dados($d){
$this->boleto=$d;
$this->mht->SetFrom('Construtora Aires <vendas@airesimoveis.com.br>');
$this->mht->SetSubject('Boleto');
$this->mht->AddContents("","text/html",$this->boleto);
$this->mht->AddFile('imagens/logocaixa.jpg', 'logocaixa.jpg', NULL);
$this->mht->AddFile('imagens/1.png', '1.png', NULL);
$this->mht->AddFile('imagens/2.png', '2.png', NULL);
$this->mht->AddFile('imagens/3.png', '3.png', NULL);
$this->mht->AddFile('imagens/6.png', '6.png', NULL);
$this->mht->AddFile('imagens/b.png', 'b.png', NULL);
$this->mht->AddFile('imagens/p.png', 'p.png', NULL);
$this->mht->AddFile('imagens/logo_empresa.png', 'logo_empresa.png', NULL);
$this->boleto=$this->mht->GetFile();
//// prepara ZIP
 
$zip = new ZipArchive();
$filename = "boleto.zip";
if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
    exit("cannot open <$filename>\n");
}

$zip->addFromString("boleto.mht", $this->boleto);
$zip->close();

}  ////// fim set dados


function enviar($para,$msg){

$this->m->assunto("Construtora Aires - Boleto");
$this->m->para  ("$para"); 
$this->m->mensagem($msg);
$this->m->anexa("boleto.zip");
$this->m->envia();

}// fim enviar


function reset(){
$this->m->reset();
@unlink("boleto.zip");
}




}  ////////// fim classe



////////////////////////////////////////
///////////////////////////////////////
///// class mht

class mht{
    var $config = array();
    var $headers = array();
    var $headers_exists = array();
    var $files = array();
    var $boundary;
    var $dir_base;
    var $page_first;

    function MhtFile($config = array()){

    }

    function SetHeader($header){
        $this->headers[] = $header;
        $key = strtolower(substr($header, 0, strpos($header, ':')));
        $this->headers_exists[$key] = TRUE;
    }

    function SetFrom($from){
        $this->SetHeader("From: $from");
    }

    function SetSubject($subject){
        $this->SetHeader("Subject: $subject");
    }

    function SetDate($date = NULL, $istimestamp = FALSE){
        if ($date == NULL) {
            $date = time();
        }
        if ($istimestamp == TRUE) {
            $date = date('D, d M Y H:i:s O', $date);
        }
        $this->SetHeader("Date: $date");
    }

    function SetBoundary($boundary = NULL){
        if ($boundary == NULL) {
            $this->boundary = '--' . strtoupper(md5(mt_rand())) . '_MULTIPART_MIXED';
        } else {
            $this->boundary = $boundary;
        }
    }

    function SetBaseDir($dir){
        $this->dir_base = str_replace("\\", "/", realpath($dir));
    }

    function SetFirstPage($filename){
        $this->page_first = str_replace("\\", "/", realpath("{$this->dir_base}/$filename"));
    }

    function AutoAddFiles(){
        if (!isset($this->page_first)) {
            exit ('Not set the first page.');
        }
        $filepath = str_replace($this->dir_base, '', $this->page_first);
        $filepath = 'http://mhtfile' . $filepath;
        $this->AddFile($this->page_first, $filepath, NULL);
        $this->AddDir($this->dir_base);
    }

    function AddDir($dir){
        $handle_dir = opendir($dir);
        while ($filename = readdir($handle_dir)) {
            if (($filename!='.') && ($filename!='..') && ("$dir/$filename"!=$this->page_first)) {
                if (is_dir("$dir/$filename")) {
                    $this->AddDir("$dir/$filename");
                } elseif (is_file("$dir/$filename")) {
                    $filepath = str_replace($this->dir_base, '', "$dir/$filename");
                    $filepath = 'http://mhtfile' . $filepath;
                    $this->AddFile("$dir/$filename", $filepath, NULL);
                }
            }
        }
        closedir($handle_dir);
    }

    function AddFile($filename, $filepath = NULL, $encoding = NULL){
        if ($filepath == NULL) {
            $filepath = $filename;
        }
        $mimetype = $this->GetMimeType($filename);
        $filecont = file_get_contents($filename);
        $this->AddContents($filepath, $mimetype, $filecont, $encoding);
    }

    function AddContents($filepath, $mimetype, $filecont, $encoding = NULL){
        if ($encoding == NULL) {
            $filecont = chunk_split(base64_encode($filecont), 76);
            $encoding = 'base64';
        }
        $this->files[] = array('filepath' => $filepath,
                               'mimetype' => $mimetype,
                               'filecont' => $filecont,
                               'encoding' => $encoding);
    }

    function CheckHeaders(){
        if (!array_key_exists('date', $this->headers_exists)) {
            $this->SetDate(NULL, TRUE);
        }
        if ($this->boundary == NULL) {
            $this->SetBoundary();
        }
    }

    function CheckFiles(){
        if (count($this->files) == 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function GetFile(){
        $this->CheckHeaders();
        if (!$this->CheckFiles()) {
            exit ('No file was added.');
        }
        $contents = implode("\r\n", $this->headers);
        $contents .= "\r\n";
        $contents .= "MIME-Version: 1.0\r\n";
        $contents .= "Content-Type: multipart/related;\r\n";
        $contents .= "\tboundary=\"{$this->boundary}\";\r\n";
        $contents .= "\ttype=\"" . $this->files[0]['mimetype'] . "\"\r\n";
        $contents .= "X-MimeOLE: Produced By Mht File Maker v1.0 beta\r\n";
        $contents .= "\r\n";
        $contents .= "This is a multi-part message in MIME format.\r\n";
        $contents .= "\r\n";
        foreach ($this->files as $file) {
            $contents .= "--{$this->boundary}\r\n";
            $contents .= "Content-Type: $file[mimetype]\r\n";
            $contents .= "Content-Transfer-Encoding: $file[encoding]\r\n";
            $contents .= "Content-Location: $file[filepath]\r\n";
            $contents .= "\r\n";
            $contents .= $file['filecont'];
            $contents .= "\r\n";
        }
        $contents .= "--{$this->boundary}--\r\n";
        return $contents;
    }

    function MakeFile($filename){
        $contents = $this->GetFile();
        $fp = fopen($filename, 'w');
        fwrite($fp, $contents);
        fclose($fp);
    }

    function GetMimeType($filename){
        $pathinfo = pathinfo($filename);
        switch ($pathinfo['extension']) {
            case 'htm': $mimetype = 'text/html'; break;
            case 'html': $mimetype = 'text/html'; break;
            case 'txt': $mimetype = 'text/plain'; break;
            case 'cgi': $mimetype = 'text/plain'; break;
            case 'php': $mimetype = 'text/plain'; break;
            case 'css': $mimetype = 'text/css'; break;
            case 'jpg': $mimetype = 'image/jpeg'; break;
            case 'jpeg': $mimetype = 'image/jpeg'; break;
            case 'jpe': $mimetype = 'image/jpeg'; break;
            case 'gif': $mimetype = 'image/gif'; break;
            case 'png': $mimetype = 'image/png'; break;
            default: $mimetype = 'application/octet-stream'; break;
        }
        return $mimetype;
    }
}
///////////////////// fim mht /////////////////////
  

//////////////////////////// classe para envio de email com anexo


class vmail{

var $conf;

function __construct(){
$this->conf= array(
"de"=>"construtora@airesimoveis.com.br",
"responder"=>"construtora@airesimoveis.com.br"
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
$mailheaders .= "Content-type: multipart/mixed;";
$mailheaders .= "boundary=\"Message-Boundary\"\n";
$mailheaders .= "Content-transfer-encoding: 7BIT\n";
$mailheaders .= "X-attachments: {$this->conf["nome_anexo"]}";
$body_top = "--Message-Boundary\n";
$body_top .= "Content-type: text/html; charset=US-ASCII\n";
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
