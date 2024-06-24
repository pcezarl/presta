<?php
class file {

	public function save($content,$to) {
		$arq = fopen($to,'w');
		fwrite($arq,$content);
		fclose($arq);
		return $to;
	}
	
	public function prepare($path, $filename) {
//		header('Content-Type: application/txt');
		header('Content-Type: text/plain');
		header('Charset: ISO-8859-1');
		header('Content-Disposition: attachment; filename='.$filename);
		header('Charset: windows-1252');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		flush();
		readfile($path.$filename);
	}
}
?>