<?php


	include "lib/var.php";
	include "lib/class_tpl.php";
	include "lib/class_db.php";

	$p=new tpl($modelo);
	$f=new tpl("tpl/tpl_form_add_apto.html");

	$d=new db;

	$d->query("select * from edificios order by ed_nome");
	if($d->status!="ok"){
		die ("ERRO ao criar lista de edificios");
	}
	$r=$d->result;

	$select="
	<select name=\"edificio\" id=\"sel\">
	<option value='' disabled='disabled' selected='selected'>SELECIONE O EDIFICIO</option>

	";

	while($m=mysql_fetch_array($r)){

		$select.="<option value='$m[id_edificio]'>$m[ed_nome]</option>\n\r";

	}//  fim loop
	$select.="</select>";


	$f->set("lista ed",$select);
	$p->set("pagina","Cadastro de Apartamento");


	$p->set("conteudo",$f->tvar());
	$p->tprint();


?>