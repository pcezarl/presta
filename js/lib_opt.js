

// funcao geral
function opt(com,id,ap){

switch(com){

case "p":paga(id,ap); return true; break;
case "d":apaga(id);return true; break;

}

}

// marca como paga
function paga(id,ap){

document.getElementById("opts").src="proc_pagar_presta.php?i="+id+"&a="+ap;

}

