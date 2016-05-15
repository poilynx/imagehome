<?php
require_once('config.php');
require_once('utils.php');
isset($CONFIG) or die("config.php not loaded");

/*断言 合法路径*/
function storage_extend_path($path) {
	global $CONFIG;
	return $CONFIG['image_root'].'/'.$path;
}

/*扩展完整路径*/
function storage_valid_path($path) {
	global $CONFIG;
	$path = trim($path);
	$match='';

	/*不存在 /../ /./ */
	
	#$reg = '/\/\s*\.{1,2}\s*\/|^\s*\.{1,2}\s*\/|\/\s*\.{1,2}\s*$/';
	$reg = '/\/\s*\.{1,2}\s*\/|^\/?\s*\.{1,2}\s*\/?|\/\s*\.{1,2}\s*\/?$|^\/?\s*\.{1,2}\s*\/?$/';
	preg_match_all($reg,$path,$match);
	if(!empty($match[0]))
		return false;
	 
	/*路径中间不存在 over文件夹*/
	$path_ex = $CONFIG['image_root'].'/'.$path;
	//die($path_ex);
	if(!file_exists($path_ex))
		return false;
	if(!is_dir($path_ex)) {
		$path = preg_replace('/\/[^\/]+$/','',$path);
	}else{
		$path = preg_replace('/\/$/','',$path);
	}
	$list = explode('/',$path);
	$cpath = $CONFIG['image_root'];
	$i = 0;
	do {
		if(is_dir($cpath.'/.over'))
			return false;
		$cpath .= '/'.$list[$i];
		$i ++;
	} while($i < count($list));
	return true;
}
/*文件夹还是文件*/
function storage_isdir($path) {
	global $CONFIG;
	$path = $CONFIG['image_root'].'/'.$path;
	if(!file_exists($path))
		die("path not found");
	if(!is_dir($path) && is_dir(dirname($path).'/.over'))
		return false;
	else if(is_dir($path))
		return true;
	else
		die("Invalid path");
}

/*获取文件或目录列表*/
function storage_list($path,$list_link = true)
{
	global $CONFIG;
	//var_dump($CONFIG);
	$list = array();
	$path = $CONFIG['image_root'].'/'.$path;
	$is_over = is_dir($path.'/.over');
	$dir = opendir($path);
	if(!$dir)
		return;
	//var_dump($list_link);
	while(($file = readdir($dir)) !== false) {
		if(is_dir($path.'/'.$file) != $is_over
				&& ( $list_link == true || !is_link($path.'/'.$file))
				&& $file!=='.info'
				&& $file!=='.over'
				&& $file!=='.' 
				&& $file!=='..') {
			$list[] = $file;
		}
	}
	sort($list, SORT_STRING | SORT_FLAG_CASE);
	return $list;

}

/*是否为终点文件夹（只能显示文件的文件夹）*/
function storage_path_end($path) {
	if(is_dir(storage_extend_path($path).'/'.'.over'))
		return true;
	return false;
}

function storage_list_dirs($path) {

}

/*读取.info文件*/
function storage_path_info($path) {
	$info_file_name = storage_extend_path($path).'/.info';
	$hf = @fopen($info_file_name,'r');
	if(!$hf)
		return NULL;
	if(!flock($hf,LOCK_SH)) {
		fclose($hf);
		die('Lock file failed.');
	}
	$json = '';
	while(!feof($hf))
		$json .= fread($hf,1024);
	flock($hf,LOCK_UN);
	fclose($hf);
	$json = json_decode($json);
	$json = object2array($json);
	return $json;
}

/*更新.info文件*/
function storage_update_path_info($path,$info) {
	$info_file_name = storage_extend_path($path).'/.info';
	$fp = fopen($info_file_name,'w') or die('Open file failed.');
	$json = json_encode($info) or die('json_encode error');
	flock($fp,LOCK_EX) or die('Lock file failed.');
	fwrite($fp,$json);
	fflush($fp);
	flock($fp,LOCK_UN);
	fclose($fp);
}

/*读取.over/info文件*/
function storage_files_info($path) {
	$path = storage_extend_path($path);
	//$basename = basename($file);
	$info_file_name = $path.'/.over/info';
	$hf = @fopen($info_file_name,'r');
	if(!$hf)
		return NULL;
	if(!flock($hf,LOCK_SH)) {
		fclose($hf);
		die('Lock file failed.');
	}
	$json = '';
	while(!feof($hf))
		$json .= fread($hf,1024);
	flock($hf,LOCK_UN);
	$json = json_decode($json);
	$json = object2array($json);
	fclose($hf);
	return $json;
}

/*更新.over/info文件*/
function storage_update_files_info($path,$info) {

	$path = storage_extend_path($file);
	//$basename = basename($file);
	$info_file_name = $path.'/.over/info';
	$fp = fopen($info_file_name,'w') or die('Open file failed.');
	$json = json_encode($info) or die('json_encode error');
	flock($fp,LOCK_EX) or die('Lock file failed.');
	fwrite($fp,$json);
	fflush($fp);
	flock($fp,LOCK_UN);
	fclose($fp);
}

/*更新某个文件的信息*/
function storage_set_file_info($file,$property,$value) {
	umask(02);
	$path = dirname(storage_extend_path($file));
	$basename = basename($file);
	$info_file_name = $path.'/.over/info';
	$fp = fopen($info_file_name,'c+') or die('Open file failed.');
	chgrp($info_file_name, "imgoper");
	chmod($info_file_name, 0664);
	if(!flock($fp,LOCK_SH)) {
		fclose($fp);
		die('Lock file failed.');
	}
	$json = '';
	while(!feof($fp))
		$json .= fread($fp,1024);
	if(empty(trim($json)))
		$json = '{}';
	$json = json_decode($json) or die('json_decode error');
	$json = object2array($json);
	$json[$basename][$property] = $value;
	flock($fp,LOCK_EX);
	$json = json_encode($json) or die('json_encode error');
	ftruncate($fp,0);
	fseek($fp,0,SEEK_SET);
	fwrite($fp,$json);
	fflush($fp);
	flock($fp,LOCK_UN);
	fclose($fp);
}


/*进行下载统计*/
function storage_do_download($file) {
	$path = dirname(storage_extend_path($file));
	$basename = basename($file);
	$info_file_name = $path.'/.over/stat';
	($fp = @fopen($info_file_name,'c+')) or die('Open file "stat" failed.');
	
	if(!flock($fp,LOCK_SH)) {
		fclose($fp);
		die('Lock file failed.');
	}
	$json = '';
	while(!feof($fp))
		$json .= fread($fp,1024);
	$json=trim($json);
	if(empty($json))
		$json = '{}';
	$json = json_decode($json) or die('json_decode error');
	$json = object2array($json);
	if(isset($json[$basename]['downloads']))
		$json[$basename]['downloads'] ++;
	else
		$json[$basename]['downloads'] = 1;

	if(!isset($json[$basename]['downloads_week']))
		$json[$basename]['downloads_week'] = array(0,0,0,0,0,0,0);
	$now = time();	
	if(isset($json[$basename]['last_time'])) {
		$days = floor(($now - strtotime($json[$basename]['last_time']))/86400);
		if($days<0) $days = 0;
	} else {
		$days = 0;
	}
	if($days>0 && $days<7) {
		if($days>6) $days=6;
		for($i=6;$i>=$days;$i--) {
			$json[$basename]['downloads_week'][$i] = $json[$basename]['downloads_week'][$i-$days];
			$json[$basename]['downloads_week'][$i-$days] = 0;
		}
	}else if($days>=7){
		$json[$basename]['downloads_week'] = array(0,0,0,0,0,0,0);
	}

	$json[$basename]['downloads_week'][0] ++ ;
	$json[$basename]['last_time'] = date('y-m-d h:i:s',$now);
	flock($fp,LOCK_EX);
	$json = json_encode($json) or die('json_encode error');
	ftruncate($fp,0);
	fseek($fp,0,SEEK_SET);
	fwrite($fp,$json);
	fflush($fp);
	flock($fp,LOCK_UN);
	fclose($fp);
}

/*获取总下载量*/
function storage_downloads($file) {
	$path = dirname(storage_extend_path($file));
	$basename = basename($file);
	$info_file_name = $path.'/.over/stat';
	$fp = @fopen($info_file_name,'r');
	if(!$fp)
		return 0;
	if(!flock($fp,LOCK_SH)) {
		fclose($fp);
		die('Lock file failed.');
	}
	$json = '';
	while(!feof($fp))
		$json .= fread($fp,1024);
	flock($fp,LOCK_UN);
	fclose($fp);
	$json=trim($json);
	if(empty($json))
		$json = '{}';
	$json = json_decode($json);
	$json = object2array($json);
	if(isset($json[$basename]['downloads']))
		return $json[$basename]['downloads'];
	else
		return 0;
}

/*周下载量*/
function storage_downloads_week($file) {
	$path = dirname(storage_extend_path($file));
	$basename = basename($file);
	$info_file_name = $path.'/.over/stat';
	$fp = fopen($info_file_name,'r');
	if(!$fp)
		return 0;
	if(!flock($fp,LOCK_SH)) {
		fclose($fp);
		die('Lock file failed.');
	}
	$json = '';
	while(!feof($fp))
		$json .= fread($fp,1024);
	flock($fp,LOCK_UN);
	fclose($fp);
	$json=trim($json);
	if(empty($json))
		$json = '{}';
	$json = json_decode($json);
	$json = object2array($json);
	if(isset($json[$basename]['downloads_week']))
		return $json[$basename]['downloads_week'];
	else
		return 0;
}

/*获取文件统计信息*/
function storage_file_stat($file) {
        $path = dirname(storage_extend_path($file));
        $basename = basename($file);
        $info_file_name = $path.'/.over/stat';
        $fp = @fopen($info_file_name,'r');
        if(!$fp){
                //die('Open file "stat" failed.');
		return array();
	}
        if(!flock($fp,LOCK_SH)) {
                fclose($fp);
                die('Lock file failed.');
        }
        $json = '';
        while(!feof($fp))
                $json .= fread($fp,1024);
        flock($fp,LOCK_UN);
        fclose($fp);
        $json=trim($json);
        if(empty($json))
                $json = '{}';
        $json = json_decode($json);
        $json = object2array($json);
        if(isset($json[$basename]))
                return $json[$basename];
        else
                return NULL;
}

?>
