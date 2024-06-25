<?php

require "nojname2.php";

conecta(); 

$r=mysql_query("select * from usuarios");

echo mysql_numrows($r);
  

?>
