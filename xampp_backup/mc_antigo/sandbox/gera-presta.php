<?php
error_reporting(E_ALL);

require "nojname2.php";

conecta(); 

$r=mysql_query("select * from usuarios");

echo mysql_numrows($r);
  

?>
