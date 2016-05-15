<?php

/*对象转数组*/
function object2array($array){
	if(is_object($array)){
		$array = (array)$array;
	}
	if(is_array($array)){
		foreach($array as $key=>$value){
			$array[$key] = object2array($value);
		}
	}
	return $array;
}

/*获取文件尺寸的可读字符串*/
function filesize_h($filename)
{
	$size = filesize($filename);

	if ($size >= 1073741824)
		$size = round($size / 1073741824 * 10) / 10 . ' GiB';
	elseif ($size >= 1048576)
		$size = round($size / 1048576 * 10) / 10 . ' MiB';
	elseif ($size >= 1024)
		$size = round($size / 1024 * 10) / 10 . ' KiB';
	elseif ($size >= 0)
		$size = $size . ' Bytes';
	else
		$size = '-';

	return $size;
}

function is_email($str) {
	preg_match('/(^[a-zA-Z_.+-]+)@([a-zA-Z_-]+).([a-zA-Z]{2,4}$)/i',$str,$matchs);
	return !empty($matchs);
}
 
?>
