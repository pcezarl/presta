<?php


	// gera grafico com as parcelas pagas e nao-pagas de um determinado apto

	include "lib/var.php";
	include "lib/func.php";

	$p= new tpl("$modelo");
	$t= new tpl("tpl/tpl_tabela_prestacoes2.html");
	$dads=new tpl("tpl/tpl_mini_painel.html");

	$dbt=new db();
	$db =new db();
	$ap =new db();


	$mytemp=mysql_connect($mysql["host"],$mysql["user"],$mysql["pass"]) or die("erro ao conectar");
	mysql_selectdb($mysql["dados"],$mytemp);


	$i=limpa($_GET["i"]);

	// pega dados do edificio
	$ap->query("select * from edificios join aptos on ap_ed=id_edificio where id_apto='$i'");
	if($ap->status=="erro")die($ap->erro);

	$ed_nome =$ap->get_val("ed_nome");
	$ap_num  =$ap->get_val("ap_num");
	$idap    =$ap->get_val("id_apto");

	$ap->destroy();

	// fim dados ed

	$sql="
	select * from prestacoes
	left join boletos on bo_presta=id_presta
	where pr_apto='$i' order by pr_vencimento
	";

	$db->query($sql);
	if($db->status=="erro")die($db->erro);

	if($db->rows==0)die("
		<h1>Server error</h1>
		Não foi possível criar um conduíte de transdobra devido a um ataque dos Borg's<hr>
		$_SERVER[SERVER_SIGNATURE]
		");

	$script=<<<OOL
<script type="text/javascript">

function imprime(){

try{
window.open("imprime-presta-apto.php?i=$i","asd","top=300,left=100,width=5,height=5");
}catch(e){
alert("erro no sistema de criacao de janelas");
}

} // fim imprime


//// re-envia boleto
function email(i){


if(confirm("Confirme o re-envio do boleto por e-mail ") ){
try{
window.open("envia-boleto.php?i="+i,"asd","top=300,left=100,width=320,height=240");
}catch(e){
alert("erro no sistema de criacao de janelas");
}

}


} ///// fim envia por email




function add_presta(){

try{
window.open("form_add_presta.php?i=$i","asd","top=300,left=100,width=650,height=180");
}catch(e){
alert("erro no sistema de criacao de janelas");
}

}
</script>
OOL;


	$cont="$script\n<h1 class='titulo'>Lista de prestações: apartamento $ap_num, edificio $ed_nome  <a href='javascript:void(0)' class='opt' onclick='add_presta()'>Adicionar prestação</a>  <a  href='javascript:void(0)' class='opt' onclick=\"imprime();\" >IMPRIMIR</a> </h1>";

	$t->begin_loop("linha");

	while($dados=mysql_fetch_object($db->result)){
		$c++;
		$cor=($c%2)?"corsim":"cornao";

		$id=$dados->id_presta;
		$ap=$dados->pr_apto;


		$pago=($dados->pr_pago=='s')?"":"<span style='color:red'>NÃO PAGA</span>";

		$bt_pagar=($dados->pr_pago=='s')?"<img src='images/bt_trans.gif' alt='' title=''  />":"<img src='images/vender.gif' border='0' alt='pagar' title='Marcar como pago' onclick=\"opt('p','$id','$ap')\" style='cursor:pointer; cursor:hand;' />";
		$bt_editar="<a href='edita_prestacao.php?i=$id' rel='facebox'><img src='images/bt_editar.gif' alt='editar' title='editar prestação' border='0' /></a>";

		if($dados->bo_ndoc==null){
			$bt_boleto="<a href='gera-boleto-unico.php?i=$id' target='_blank'><img src='images/bt_boleto.gif' alt='boleto' title='gerar boleto' border='0' /></a>";
		}else{
			$bt_boleto="<a href='javascript:void(0)' onclick=\"email('{$dados->id_presta}') \" ><img src='images/bt_reenvia.png' alt='boleto' title='reenviar boleto' border='0' /></a><a href='ver-boleto-unico.php?i=$id' target='_blank'><img src='images/bt_ver_boleto.gif' alt='boleto' title='ver boleto' border='0' /></a>";
		}


		$dbt->query("select max(pr_num) as idx from prestacoes where pr_tipo='{$dados->pr_tipo}' and pr_apto='$i' ");
		$max_presta=$dbt->get_val("idx") ;
		$tipo="<span style='font-size:7pt;font-family:verdana'>".$dados->pr_num."/".$max_presta. "</span> " . $tipo_parcela[$dados->pr_tipo];


		$valor=mil($dados->pr_valor);
		$data =mydata($dados->pr_vencimento);

		$data_pg =(($dados->pr_data_pago=="0000-00-00") || ($dados->pr_data_pago=="") )?"":mydata($dados->pr_data_pago);

		$tab["cor"]=$cor;
		$tab["data"]=$data;
		$tab["valor"]=$valor;
		$tab["data_pg"]=$data_pg;
		$tab["pago"]=$pago;
		$tab["tipo"]=$tipo;
		$tab["obs"]=$dados->pr_obs;
		$tab["bt pagar"]=$bt_pagar;
		$tab["bt editar"]=$bt_editar;
		$tab["bt boleto"]=$bt_boleto;


		if($dados->bo_ndoc==null){
			$tab["doc"]="";
		}else{
			$tab["doc"]=$dados->bo_ndoc;
		}


		$t->set_loop($tab);
	}
	$t->end_loop("linha");




	$dv=date("Y-m-d");
	$db->reset();



	$db->query("select sum(pr_valor) as devido from prestacoes where  pr_pago='s' and pr_apto='$ap'" );
	if($db->status=="erro")die($db->erro."<hr>".$db->sql);
	$total_pago=mil($db->get_val("devido"));
	$db->reset();

	$db->query("select sum(pr_valor) as devido from prestacoes where  pr_pago='n' and pr_apto='$ap'" );
	if($db->status=="erro")die($db->erro."<hr>".$db->sql);
	$total_devido=mil($db->get_val("devido"));
	$db->reset();

	$dads->set("texto","Total pago:R\$$total_pago<br />Total devido: R\$$total_devido");



	$cont.=$t->tvar()."
	<!-- imagem do grafico -->
	<img src='charts/chart-presta-apto.php?i=$idap' alt='' />
	{$dads->tvar()}
	<div style='text-align:center'><img src='images/fim-tabela.png' alt=''/></div>
	<!-- FIM DA TABELA DAS PRESTAÇÕES -->
	";

	$p->set("pagina","Detalhes das prestações");
	$p->set("conteudo",$cont);
	$p->tprint();

?>
