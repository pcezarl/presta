<?php
// classe das parcelas

class parcela{

public $parcela;
static private $cont=0;


public function __construct(){

$this->cont=0;

}


public function add($valor,$data)
{
$this->parcela[$this->cont]["valor"]=$valor;
$this->parcela[$this->cont]["data"]=$data;
$this->cont++;

//

}

public function test(){

var_dump($this->parcela);
}


public function get(){
return $this->parcela;

}


}; //fim classe
  



?>
