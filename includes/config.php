<?php
//$_SERVER['DOCUMENT_ROOT'] = @$_SERVER['DOCUMENT_ROOT'] ?: dirname(__FILE__).'/../..';
//$document_root = @$_SERVER['DOCUMENT_ROOT'] ?: dirname(__FILE__).'/../..';
//$www_offset = '/imagehome';
$CONFIG=array(
	'www_root_r'=>'',
	'image_root'=>'/Library/WebServer/Documents/data/images',//镜像文件根目录
	'log'=>'/tmp/nis.log',//日志文件
	'template'=>'default',
	'guestbook_root'=>'/Library/WebServer/Documents/data/guestbook'
);
//unset($document_root);
?>
