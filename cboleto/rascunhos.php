<?php

$max_linha=5;// 5 registros por linha
for($c=0;$c<=100;$c++){
$br=(is_int($c/$max_linha))?"<br />":"";  
echo "$c- $br\n";
}
?>


<?php exit()?>
<?php echo $dadosboleto["linha_digitavel"]?>
<?php echo $dadosboleto["valor_boleto"]?>
<?php echo $dadosboleto["identificacao"]; ?>
<?php echo isset($dadosboleto["cpf_cnpj"]) ? "<br>".$dadosboleto["cpf_cnpj"] : '' ?> // 
<?php echo $dadosboleto["endereco"]; ?>
<?php echo $dadosboleto["cidade_uf"]; ?>
<?php echo $dadosboleto["linha_digitavel"]?>
<?php echo $dadosboleto["cedente"]; ?>
<?php echo $dadosboleto["agencia_codigo"]?>
<?php echo $dadosboleto["especie"]?>
<?php echo $dadosboleto["quantidade"]?>
<?php echo $dadosboleto["nosso_numero"]?>
<?php echo $dadosboleto["numero_documento"]?>
<?php echo $dadosboleto["cpf_cnpj"]?>
<?php echo $dadosboleto["data_vencimento"]?>
<?php echo $dadosboleto["valor_boleto"]?>
<?php echo $dadosboleto["sacado"]?>
<?php echo $dadosboleto["demonstrativo1"]?>
<?php echo $dadosboleto["demonstrativo2"]?>
<?php echo $dadosboleto["demonstrativo3"]?>
<?php echo $dadosboleto["data_documento"]?>
<?php echo $dadosboleto["especie_doc"]?>
<?php echo $dadosboleto["aceite"]?>
<?php echo $dadosboleto["data_processamento"]?>
<?php echo $dadosboleto["carteira"]?>
<?php echo $dadosboleto["valor_unitario"]?>
<?php echo $dadosboleto["instrucoes1"]; ?>
<?php echo $dadosboleto["instrucoes2"]; ?>
<?php echo $dadosboleto["instrucoes3"]; ?>
<?php echo $dadosboleto["instrucoes4"]; ?>
<?php echo $dadosboleto["endereco1"]?>
<?php echo $dadosboleto["endereco2"]?>
<?php fbarcode($dadosboleto["codigo_barras"]); ?> // tag: barras
<?php echo $dadosboleto["codigo_banco_com_dv"]?>
