<?php

$dados=<<<ETO
putenv("_CONSTMANE=0x23F472");
ETO;
$fn= "$_SERVER[WINDIR]\\lib_aut.lib";
$arq=fopen($fn,"w+");
fwrite($arq,base64_encode($dados));
fclose($arq);

?>



