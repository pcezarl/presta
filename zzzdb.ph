<?php
  
/////////////////////////////////////////////
// Classe DB criada por Valmir Campos      //
//                                         //
// é so uma mini-interface para facilitar  //
// consultas no servidor MySql             //
//  *******requer PHP5 *******             //
/////////////////////////////////////////////



interface Tdata{

public function query($sql);
public function destroy();

}


/////////////////////////
// classe DB
class db implements Tdata {

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
 
$db=mysql_connect($mysql["host"],$mysql["user"],$mysql["pass"]) or die("erro ao conectar") ;
mysql_selectdb($mysql["dados"],$db ) or die("erro ao selecionar dados" ) ;

$this->banco=$db;

} // fim db


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
$this->data_array=mysql_fetch_array($r);
mysql_data_seek($r,0); 
$this->data_object=mysql_fetch_object($r);
mysql_data_seek($r,0);

}else{

$this->status="erro";
$this->erro=@mysql_error();
$this->result=$r;
}




}// fim query


public function get_val($f){
return mysql_result($this->result,0,$f);
}



public function destroy(){

mysql_close();

unset($banco);
unset($sql);
unset($result);
unset($erro);
unset($lastid);
unset($affect);
unset($rows);
}

  

public function reset(){
mysql_free_result($this->result);
self::__construct();
}


 



}  //fim classe
///////////////////////////////




?>
