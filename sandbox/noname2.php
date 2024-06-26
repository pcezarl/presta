<?php
error_reporting(E_ALL);

$i=$_POST["aa"];

$str = "teste  $i <b>bold</b>\n \n";


echo addslashes( $str );

?> 

<form method="post">
<input type="text" name="aa"><br />
<input type="submit">
</form>