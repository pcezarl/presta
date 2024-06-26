<?php
class file {

	public function save($content,$to) {
		$arq = fopen($to,'w');
		fwrite($arq,$content);
		fclose($arq);
		return $to;
	}
	
	public function prepare($path, $filename) {
	   header('Content-Type: application/octet-stream');
	   header('Content-Disposition: attachment; filename='.$filename);
	   header('Charset: UTF-8');
	   header('Expires: 0');
	   header('Cache-Control: must-revalidate');
	   header('Pragma: public');
	   readfile($path.$filename);
		
	}
}
?>