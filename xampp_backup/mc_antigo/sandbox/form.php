<?php

session_start();

$_SESSION["aaa"]="flavia safada";

header("location:envia.php");


if($_POST["opt"]){

$caminho_absoluto = "C:\\temp";

$nome_arquivo = $_FILES['arquivo']['name'];
$tamanho_arquivo = $_FILES['arquivo']['size'];
$arquivo_temporario = $_FILES['arquivo']['tmp_name'];

move_uploaded_file($arquivo_temporario, "$caminho_absoluto/$nome_arquivo");


}
?>
<hr>
<form method="post" enctype="multipart/form-data">
<input type="file" name="arquivo">
<input type="hidden" name="opt" value="nnnnnnnnnnnnn">
<input type="submit" value="enviar dadso">

</form>

</body>
</html>
