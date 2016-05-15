<?php
include_once('includes/config.php');
/*写入日志*/
function write_log($str){
	global $CONFIG;
	if(!isset($CONFIG['log']))
		return;
	$fp = fopen($CONFIG['log'],'a+');
	if(!$fp)
		return;
	if(flock($fp,LOCK_EX)==FALSE)
		return;
	fputs($fp,$str."\n");
	flock($fp,LOCK_UN);	
	fclose($fp);
	
}

?>
