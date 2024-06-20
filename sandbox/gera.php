<pre>
<?php

include "../lib/func.php";

$teste="eNqFzksKg0AQhOG79AGkuufhpOY0DdkIgYBKNjJ3d5RkJZhd/ZuPcgZuE1Gd+hvGbWGifPz1nqUuHCmWgCHhqEh5+upSJ6rlGEosQG2t563St2KwixFCHh+av4b9e6LoT3BRUBLMcCptB55VOis=
";

$t=str_decode($teste);

//var_dump($t);




$rr=array_simply($t);


$tt=0;


foreach($rr as $dd){
$data=date("d/m/Y",$dd["data"]);
echo "Data:$data - Valor:$dd[valor]<br />";
$tt+=$dd["valor"];
}
echo "Valor total:". mil($tt);

?>
