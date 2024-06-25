<?php /// Valmirez System - valmirez@hotmail.com

        //data: 24 abril 2008 

        class tpl{ //comeco classe

        var $dados;
        var $templ;
        var $loop;
        var $temp;
        var $temp2;
        var $p;
        var $escs;

        function tpl($a){// init

        try{
        $t=file($a);
        if(!$t)throw new Exception("<b>classe template diz:</b> tentei abrir o arquivo <i>$a</i>, mas nao consegui<br>");
        $this->dados=implode('',$t);
        $this->templ=$this->dados;
        }catch(Exception $e){
        echo $e->getMessage();
        }



        }//fim init

        function set($a,$p){ //inicio set
        $this->dados=ereg_replace("(\{\{$a\}\})","$p",$this->dados);
        } // fim set

        function tprint(){
        print($this->tvar());
        }

        function tdie(){
        die($this->tvar());
        }

        function tvar(){
        $this->phptags();
        if($this->escs){
        foreach($this->escs as $v){
        $this->dados=eregi_replace("<!--esc:$v -->(.*)<!--endesc:$v -->","",$this->dados);
        }
        }

        //return $this->acentos($this->dados);
        return $this->dados;

        }


        function treset(){
        $this->dados=$this->templ;
        }

        function begin_loop($l){ //inicio funcao loop
        @eregi("<!--loop:$l -->(.*)<!--end loop:$l -->", $this->dados, $saida);

        $this->loop=$saida[1];
        $this->p=$saida[1];

        } // fim funcao loop

        function set_loop($t){

        foreach($t as $tag=>$val){
        $this->loop=@ereg_replace("(\{\{$tag\}\})","$val",$this->loop);
        }

        $this->temp.=$this->loop;
        $this->loop=$this->p;

        }

        function end_loop($t){

        $this->dados=ereg_replace("<!--loop:$t -->(.*)<!--end loop:$t -->",$this->temp,$this->dados);
        unset($this->temp);
        }


        function esc($i){
        $this->escs[]=$i;
        }

        function phptags(){
        preg_match_all("<!--php:(.*.php) -->", $this->dados,$regs);
        $regs=$regs[1];

        foreach($regs as $arq){
        $t= $this->parse($arq);
        $this->dados=eregi_replace("<!--php:$arq -->","$t",$this->dados);
        }

        }


        function parse($arquivo){
        ob_start();
        if(!file_exists($arquivo)){
        print("<b>KERNEL PANIC:</b> erro ao tentar incluir um script (o arquivo <u>$arquivo</u> não existe).\n");}
        else{
        eval("require_once(\"$arquivo\");");
        }

        $temp=ob_get_contents();
        ob_end_clean();
        return $temp;
        }


        function tinclude($tag,$arq){

        $ta=$this->parse($arq);

        $this->dados=ereg_replace("(\{\{$tag\}\})","$ta",$this->dados);

        }


        function arquivo($tag,$arquivo){

        $f=@fopen($arquivo,"r")or die ("<b>Class TPL diz:</b> ERRO ao tentar inserir arquivo.");
        $dat=fread($f,filesize($arquivo));

        $this->dados=ereg_replace("(\{\{$tag\}\})","$dat",$this->dados);

        fclose($f);


        }// fim do arquivo




        //////// funcao interna para trocar acentos por entidade HTML
        function acentos($str){

        $acentos = array(
        '/á/' => '&Aacute;',
        '/Á/' => '&aacute;',

        '/ã/' => '&atilde;',
        '/Ã/' => '&Atilde;',

        '/â/' => '&Acirc;',
        '/Â/' => '&acirc;',

        '/à/' => '&Agrave;',
        '/À/' => '&agrave;',

        '/é/' => '&eacute;',
        '/É/' => '&Eacute;',

        '/í/' => '&iacute;',
        '/Í/' => '&Iacute;',

        '/ó/' => '&oacute;',
        '/Ó/' => '&Oacute;',

        '/õ/' => '&otilde;',
        '/Õ/' => '&Otilde;',

        '/ç/' => '&ccedil;',
        '/Ç/' => '&Ccedil;'

        );
        return preg_replace(array_keys($acentos),$acentos , $str);
        } ////// fim acentos
}// fim classe
?>