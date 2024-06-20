<?php
  
/////////////////////////////////////////////
// Classe DB criada por Valmir Campos      //
//  valmirez@hotmail.com                   //
// � so uma mini-interface para facilitar  //
// consultas no servidor MySql             //
//  ******* requer PHP5 *******            //
/////////////////////////////////////////////


interface Tdata{

public function query($sql);
public function destroy();
public function get_val($f);
public function reset();
public function transact($opt);
public function lock($tb,$md="escrita");
public function unlock();

} // fim interface


/////////////////////////
// classe DB
class db implements Tdata {



private $opts=array(
"abre"    =>"start transaction",
"salva"   =>"commit",
"cancela" =>"rollback"
);

private $pre_opts=array(
"abre"   =>"set autocommit=0",
"salva"  =>"set autocommit=1",
"cancela"=>"set autocommit=1"
);

private $banco; 
public  $sql;
public  $result;
public  $erro;
public  $lastid;
public  $affect;
public  $rows;
public  $data_array;
public  $data_object;



public function __construct(){
global $mysql;
 
$db=mysql_connect($mysql["host"],$mysql["user"],$mysql["pass"]) or die("BD::erro ao conectar") ;
mysql_selectdb($mysql["dados"],$db ) or die("DB::erro ao selecionar dados" ) ;

$this->banco=$db;

} // fim db


public function __destruct(){
mysql_query("commit",$this->banco);
mysql_query("unlock tables",$this->banco);
mysql_query("flush tables",$this->banco);
self::destroy();
}



public function query($sql){

$this->sql=$sql;

$r=mysql_query($sql,$this->banco) ;
$ee=mysql_errno();

if($ee==0){
$this->result=$r;
$this->status="ok";
$this->affect=@mysql_affected_rows();
$this->lastid=@mysql_insert_id();
$this->rows=@mysql_numrows($r);
mysql_data_seek($this->result,0);
$this->data_array=mysql_fetch_array($this->result);
mysql_data_seek($this->result,0); 
$this->data_object=mysql_fetch_object($this->result);
mysql_data_seek($this->result,0);

}else{

$this->status="erro";
$this->erro=@mysql_error();
$this->result=$r;
}


}// fim query


public function get_val($f,$i=0){
return mysql_result($this->result,$i,$f);
}



public function destroy(){

mysql_close($this->banco);
unset($banco);
unset($sql);
unset($result);
unset($erro);
unset($lastid);
unset($affect);
unset($rows);
}  // fim destroy

  

public function reset(){
mysql_free_result($this->result);
self::__construct();
}


/*
Lista de opcoes para transacoes
abre = cria uma transacao
salva = grava as alteracoes em disco
cancela = anula as operacoes anteriores
*/

public function transact($opt){
if(!array_key_exists($opt,$this->opts))die("<b>Class DB::</b> comando transact n�o reconhecido: <i>$opt</i>.");

mysql_query($this->pre_opts[$opt],$this->banco);
mysql_query($this->opts[$opt],$this->banco);

} // fim transact

 
// bloqueia tables
public function lock($tb,$md="escrita"){
$modo["escrita"]="write";
$modo["leitura"]="read";
mysql_query("lock table $tb $modo[$md]",$this->banco);
} // fim lock


// desbloqueia tables
public function unlock(){
mysql_query("unlock tables",$this->banco);
mysql_query("flush tables", $this->banco);
} // fim unlock


}  //fim classe
///////////////////////////////




?>
