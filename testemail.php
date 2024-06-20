<?php

include "lib/class_vmail.php";


$m=new vmail();
$m->para("kk@pop.com.br");
$m->assunto("Teste de boleto");
$m->mensagem("minha mensagem auiq");
$m->anexa("boleto.zip");
$m->envia();
$m->reset();

$m->para("sss@gmail.com");
$m->assunto("Teste de boleto");
$m->mensagem("minha mensagem auiq");
$m->anexa("boleto.zip");
$m->envia();
$m->reset();

$m->para("vfff@hotmail.com");
$m->assunto("Teste de boleto");
$m->mensagem("minha mensagem auiq");
$m->anexa("boleto.zip");
$m->envia();
$m->reset();







?>
