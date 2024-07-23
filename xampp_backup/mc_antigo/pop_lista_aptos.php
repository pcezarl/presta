<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt-br" lang="pt-br">
	<head>

		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta http-equiv="content-language" content="pt-br" />
		<title>Busca de apartamento</title>

		<script type="text/javascript">

		function seleciona(i){

		window.opener.document.getElementById("apto").value=i;
		self.close();
		}

		</script>

		<style type="text/css">
			body{
				margin:0 auto;
				font-family: sans-serif;
				font-size: 10pt;
			}

			a:link, a:visited{
				color:black;
				text-decoration: none;
				padding-left: 20px;
				padding-right: 20px;
				text-align: center;
				border:1px solid #B17C01;
				background-color: #FFF1BF;
				margin-left: 3px;
				margin-top: 5px;
				margin-bottom: 5px;
			}

			a:hover{
				background-color: #FEB325;
			}

		</style>

	</head>
	<body>
		<?php
			////// procura id de apartamento

			include "lib/var.php";
			include "lib/func.php";

			function edificios(){
				$db= new db();

				$db->query("select * from edificios");

				// pre loop
				echo <<<OLP
selecione o edificio:<hr />
<div style="display:block;width:500px;height:300px;border:1px solid red;overflow:auto">
OLP;

				// loop
				while($dados=mysql_fetch_assoc($db->result)){
					echo "<a href=\"?i=$dados[id_edificio]\">$dados[ed_nome]</a><br> ";

				}


				// pos loop
				echo <<<OLP
</div>
OLP;


			} // fim edificios


			function apto($i){
				$db= new db();

				$db->query("select * from aptos where ap_ed='$i'");
				if($db->status=="erro")die ($db->erro);


				// pre loop
				echo <<<OLP
selecione o APARTAMENTO:<hr />
<div style="display:block;width:500px;height:300px;border:1px solid red;overflow:auto">
OLP;

				// loop
				$c=0;
				while($dados=mysql_fetch_assoc($db->result)){
					$c++;
					$br=(is_int($c/8) )?"<br /><br />":" ";
					echo "<a  href=\"javascript:void(0)\" onclick=\"seleciona('$dados[id_apto]')\">$dados[ap_num]</a>$br";

				}



				// pos loop
				echo <<<OLP
</div>
OLP;





			} // fim apto




			if($_GET["i"]){
				apto($_GET["i"]);

			}else{
				edificios();
			}



		?>

	</body>
</html>
