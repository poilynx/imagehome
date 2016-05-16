<?php
/**
 * download 断点续传
 * @param string $path: 文件路径
 * @param string $file: 文件名
 * @return void
 */
function download($path,$file) {
	//flush();
	$real = $path.'/'.$file;
	if(!file_exists($real)) {
		return false;
	}
	$fp = fopen($real,'rb+') or die("Open file failed.");
	$size = filesize($real);
	$size2 = $size-1;
	$range = 0;
	if(isset($_SERVER['HTTP_RANGE'])) {
		//header('HTTP /1.1 206 Partial Content');
		$range = str_replace('=','-',$_SERVER['HTTP_RANGE']);
		$range = explode('-',$range);
		$range = trim($range[1]);
		header('Content-Length:'.$size);
		header('Content-Range: bytes '.$range.'-'.$size2.'/'.$size);
	} else {
		header('HTTP /1.1 206 Partial Content');
		header('Content-Length:'.$size);
		header('Content-Range: bytes 0-'.$size2.'/'.$size);
	}
	#header('Content-type: application/octet-stream');
	header('Accenpt-Ranges: bytes');
	header("Cache-control: public");
	header("Pragma: public");
	//解决在IE中下载时中文乱码问题
	$ua = $_SERVER['HTTP_USER_AGENT'];

	$filename = $file; 
	$encoded_filename = urlencode($filename); 
	$encoded_filename = str_replace("+", "%20", $encoded_filename); 
	
	header('Content-Type: application/octet-stream'); 
	if (preg_match("/MSIE/", $ua)) { 
		header('Content-Disposition: attachment; filename="' . $encoded_filename . '"'); 
	} else if (preg_match("/Firefox/", $ua) ) { 
		header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"'); 
	} else { 
		header('Content-Disposition: attachment; filename="' . $filename . '"'); 
	} 
	
	fseek($fp,$range);
	set_time_limit(0);
	
	while(!feof($fp)) {
		print(fread($fp,1024));
		flush();
		ob_flush();
	}
	
	fclose($fp);
		
	exit();
}
?>
