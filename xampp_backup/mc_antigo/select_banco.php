<?php

	include 'lib/func.php';
	include 'lib/var.php';
	include 'lib/class_db.php';
	
	session_start();
	if($_POST){
		$data = explode(' - ', $_POST['conta']);
		$_SESSION['conta'] = $data[1];

		$sql = "SELECT * FROM contas WHERE conta = $data[1]";
		$db=new db();
		$db->query($sql);
		while($d=mysql_fetch_object($db->result)){
			$_SESSION['conta_id'] = $d->id;
			$_SESSION['banco'] = $d->banco;
		}
		echo json_encode(array('status'=>'success'));
		exit;
	}	
?>
