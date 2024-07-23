<?php

        /////// visualiza boleto emitido

        include "lib/var.php";
        include "lib/func.php";


        $i=limpa($_GET["i"]);

        $db=new db;




        if( ($_POST["id"]) && ($_POST["data"] ) )
        {

                $mail=new envia_boleto();

                $alt=new db();

                $dv=data2mysql($_POST["data"]);
                $sql_alt="update boletos set bo_data_vence='$dv' where bo_presta='$_POST[id]' ";
                //die("$sql_alt");
                $alt->query($sql_alt);
                if($alt->status=="erro")die($alt->erro);



                $sql="select * from boletos
                left join clientes   on bo_prop     =id_cliente
                left join aptos      on id_apto     =bo_apto
                left join edificios  on id_edificio =ap_ed
                left join prestacoes on bo_presta   =id_presta
                where bo_presta='$i'
                ";
                $db->query($sql);
                if($db->status=="erro")die($db->erro);
                $d=$db->data_object;


                $b= new boleto_HSBC();

                $tipo=$tipo_parcela[$d->pr_tipo];

                $db->query("select max(pr_num) as idx from prestacoes where pr_apto='{$d->pr_apto}' and pr_tipo='{$d->pr_tipo}' ");
                $idx=$db->get_val("idx");

                $b->set("demonstrativo1","parcela {$d->bo_num_presta} de $idx  ($tipo) ");
                $b->set("demonstrativo2","Edificio {$d->ed_nome} apto: {$d->ap_num}");
                $b->set("demonstrativo3","Taxa do boleto: R\$ ".mil($b->taxa_boleto));
                $b->set("demonstrativo4","Construtura Aires - construindo seu sonho da casa propria");
                $b->set("endereco1","{$d->cli_rua},{$d->cli_numero} - {$d->cli_bairro}");
                $b->set("endereco2","{$d->cli_cidade} - {$d->cli_estado} -CEP:{$d->cli_cep}");
                $b->val("valor_boleto",$d->bo_valor);
                $b->val("data_vencimento",mydata($d->bo_data_vence));
                $b->val("numero_documento",$d->bo_ndoc);
                $b->set("data_documento",date("d/m/Y"));
                $b->set("sacado",$d->cli_nome);
                $b->draw();


                $boleto=str_replace("imagens/","",$b->layout);
                $valor=mil($d->pr_valor);
                $msg="
                Segue em anexo<br />
                boleto referente ao pagamento de parcela do apartamento {$d->ap_num} do edificio {$d->ed_nome}<br>
                Valor da prestação: R\${$valor}<br /><br /><br />
                caso tenha problemas na visualização do boleto, utilize a linha digitavel:<br /><br />
                <span style='padding:3px;background:#C0C0C0;border:1px solid;#585858;font-family:courier;font-size:11pt;color:black;font-weight:bold'>{$b->dadosboleto[linha_digitavel]} </span>
                ";

                $mail->set_dados($boleto);
                $mail->enviar($d->cli_email,$msg);
                $mail->reset();

                $res= <<<AAA
OK!!Boleto re-enviado.
<a href="javascript:void(self.close())" class="aqua2">Fechar</a>

AAA;


        }// fim envio de boleto
        else // formulario de alteracao de data
        {

                $sql="select * from boletos where bo_presta='$i' ";
                $db->query($sql);
                if($db->status=="erro")die($db->erro);
                $d=$db->data_object;



                $data=mydata($d->bo_data_vence);
                $res=<<<OOO

                <form method="post" id="aaa">
<input type="hidden" name="id" id="id"  value="{$d->bo_presta}"  />
Vencimento:<input type="text" name="data" id="data"  value="{$data}"  /><br /><br />
<a href="javascript:void(0)" onclick="document.getElementById('aaa').submit()" class="aqua4" >Enviar</a>
</form>

OOO;




        }// fim form alteracao

?>
<html>
        <head>
                <meta http-equiv="pragma" content="no-cache">
                <meta http-equiv="cache-control" content="no-cache">
                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                <meta name="generator" content="PhpED Version 5.9 (Build 5921)">
                <title>Envio de Boleto</title>

                <style type="text/css">
                        @import url("style.css");


                        #info{
                                padding-top:15px;
                                margin-top:20px;
                                color:black;
                                background-color: #B5FE47;
                                font-family:verdana;
                                font-size:14pt;
                                height: 100px;
                                border:3px solid #79C901;
                        }

                </style>

        </head>
        <body>
                <br /><br />
                <div style="background:white;margin:0 auto;width:95%">
                        <?php echo $res?>
                </div>

        </body>
</html>