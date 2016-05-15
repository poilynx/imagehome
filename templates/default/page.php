<?php
//$THOME = dirname(__FILE__);
require_once($THOME.'/init.php');
include($THOME.'/header.php');
switch($PAGE['show']){
	case 'view':
		$title = "View";
		include($THOME.'/inner_title.php');
		include($THOME.'/search_panel.php');
		include($THOME.'/browser.php');
		break;
	case 'file_info':
		$title = "Download";
		include($THOME.'/inner_title.php');
		include($THOME.'/file_info.php');
		break;
	case 'about':
		$title = "About";
		include($THOME.'/inner_title.php');
		include($THOME.'/about.php');
		break;
	case 'search':
		$title = "Search";
		include($THOME.'/inner_title.php');
		include($THOME.'/search_panel.php');
		include($THOME.'/search_result.php');
		break;
	case 'guestbook':
		$title = "Guest book";
		include($THOME.'/inner_title.php');
		include($THOME.'/guestbook.php');
		break;
	case 'messagebox':
		//$title = $PAGE['messagebox']['text'];
		include($THOME.'/messagebox.php');
		break;
	default:
		echo 'Unknow page.';
}
include($THOME.'/footer.php');
?>
