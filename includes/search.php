<?php
require_once('includes/config.php');
require_once('includes/image_storage.php');
function search($path,$keywords) {
	//$keyword_arr = explode(' ',$keyword);
	$resultSet = array();
	if(storage_path_end($path)) {
		$list = storage_list($path,false);
		$info = storage_files_info($path);
		foreach($list as $item) {
			$nfound = 0;
			$tmp_result = array(
					'filename'=>$item,
					'comment'=>@$info[$item]['comment'],
					'path'=>$path
					);
			foreach($keywords as $keyword) {
				//if(strlen($keyword) < 2)
				//	continue;
				if(stristr($item,$keyword)!==FALSE 
						|| (isset($info[$item]['comment'])
							&& stristr($info[$item]['comment'],$keyword)!==FALSE)) {
					$nfound ++;
				}
				if($nfound >= count($keywords))
					$resultSet[] = $tmp_result;
			}
		}
	}else{
		$list = storage_list($path,false);
		is_array($list) or $list = array();
		foreach($list as $item) {
			$resultSet = array_merge($resultSet,search($path.'/'.$item,$keywords));
		}
	}
	return $resultSet;
}
?>
