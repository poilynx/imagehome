<?php
function get_icon_url_by_filename($filename) {
	global $CONFIG;
	$arr = explode('.', $filename);
	$suf = end($arr);
	switch(strtolower($suf)){
		case 'iso':
			$iconfile = 'file_iso.png';
			break;
		case 'zip':
		case 'tar':
		case 'bz2':
		case 'gz':
		case 'rar':
		case '7z':
		case 'xz':
			$iconfile = 'file_zip.png';
			break;
		default:
			$iconfile = 'file_default.png';
	}
	$icon_url = $CONFIG['www_root_r'].'/templates/'.$CONFIG['template'].'/images/'.$iconfile;
	return $icon_url;
}
?>
