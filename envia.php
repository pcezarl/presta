<?php
#modificar nesta variavel o email que irá receber
$para = "".$_POST['email']." \n";
$mensagem = "".$_POST['html']."\n";
$headers = "From: email@dominio";
$headers .= "Reply-To: email@dominio";
$headers .= "X-Mailer: PHP v".phpversion()."\n";
if (mail($para,$mensagem,$headers)) {
echo "Emails enviados com sucesso.";
}
else {
echo "Erro no envio do email!";
}
?>