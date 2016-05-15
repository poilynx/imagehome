<?php
//ini_set("display_errors",1);
//ini_set("error_reporting",E_ALL);
ini_set('date.timezone','Asia/Shanghai');
require_once('includes/config.php');
require_once('includes/image_storage.php');
require_once('includes/savefile.php');
require_once('includes/log.php');
require_once('includes/search.php');
require_once('includes/guestbook.php');
//$_INDEX = $_SERVER['PHP_SELF'];
$_INDEX = $CONFIG['www_root_r'].'/';

$ACTIONS = array(
	'view'=>'view',
	'file_info'=>'image',
	'download'=>'save',
	'about'=>'about',
	'search'=>'search',
	'guestbook'=>'guestbook',
	'leave_message'=>'leave_message'
);

if(!isset($_GET['action']))
	$_GET['action'] = $ACTIONS['view'];

$PAGE = array();//Page data set



$PAGE['title'] = 'Image Home - ';
$PAGE['about_url'] = $_INDEX."?action=".$ACTIONS['about'];



switch($_GET['action']){
	case $ACTIONS['view']:
		$path = isset($_GET['path'])?trim($_GET['path']):'/';
		//die($path);
		$path = preg_replace('/\/{2,}/','/',$path);
		$path = preg_replace('/^\/|\/$/','',$path);
		if(!storage_valid_path($path)) die("Invalid path...".$path);
			
		$PAGE['title'] .= $path;
		if (storage_isdir($path)) {
			$PAGE['show']='view';
			$browser = array();
			/*路径链接列表*/
			$browser['path'][0] = array('text'=>'ROOT','url'=>"$_INDEX?action=${ACTIONS['view']}&path=/");
			$cpath = '';
			foreach(explode('/',$path) as $item) {
				if(empty($item))
					continue;
				$cpath .= '/'.$item;
				$browser['path'][] = array(
						'text'=>$item,
						'url'=>"$_INDEX?action={$ACTIONS['view']}&path=$cpath"
						);
			}
			if(is_dir(storage_extend_path($path).'/.over')) {
				$browser['show'] = 0;
				$info = storage_files_info($path);
				$list = storage_list($path);
				$browser['items'] = array();
				foreach($list as $item) {
					$value=@$info[$item]?:array();
					$filename = storage_extend_path($path).'/'.$item;
					write_log($filename);
					$browser['items'][] = array(
							'text'=>$item,
							'size'=>filesize_h($filename),
							'comment'=>@$value['comment']?:'',
							'downloads'=>storage_downloads($path.'/'.$item),
							'url'=>"$_INDEX?action={$ACTIONS['file_info']}&path=$path/$item"
							);
				}
			} else {
				$browser['show'] = 1;
				$list = storage_list($path);
				$browser['items'] = array();
				foreach($list as $item) {
					$item_info = storage_path_info($path.'/'.$item);
					$browser['items'][] = array(
							'text'=>"$item",
							'comment'=>@$item_info['comment']?:'',
							'url'=>$_INDEX."?action={$ACTIONS['view']}&path=".$path.'/'.$item
							);
					unset($item_info);
				}
				unset($list);
			}
			$PAGE['browser'] = $browser;
			unset($browser);
			/*
			   echo '<pre>';
			   var_dump($PAGE);
			   echo '</pre>';
			 */
		} else {
			$PAGE['show'] = 'download';
			$PAGE['filename'] = 'filename';
			storage_do_download($path);
			//echo "Download: ".$_GET['path']."\n";
		}
		break;
	case $ACTIONS['file_info']:
		$path = isset($_GET['path'])?trim($_GET['path']):'/';
                $path = preg_replace('/\/{2,}/','/',$path);
                $path = preg_replace('/^\/|\/$/','',$path);
		if(!storage_valid_path($path)) die("Invalid path..");
		$filename = storage_extend_path($path);
		if(is_dir($filename)) die('filename specified is not a directory');
                $PAGE['title'] .= "Download - ".$path;

		$PAGE['show'] = 'file_info';
		$stat = storage_file_stat($path);
		$files_info = storage_files_info(dirname($path));
		$info = @$files_info[basename($path)];
		$file_size = filesize($filename);
		unset($files_info);
		$file_info = array(
			'filename'=>basename($path),
			'url'=>"$_INDEX?action={$ACTIONS['download']}&path=$path",
			'size'=>$file_size . ' Bytes' . ($file_size > 1024 ? ' = '.filesize_h($filename) : ''),
			'comment'=>@$info['comment']?:'',
			'md5'=>@$info['md5']?:'unknow.',
			'downloads'=>@$stat['downloads']?:0,
			'downloads_week'=>@$stat['downloads_week']?:array(0,0,0,0,0,0,0),
			'last_time'=>@$stat['last_time']?:'Never',
			'create_time'=>date('Y-m-d H:i:s',filectime($filename))
		);
		$PAGE['fileinfo'] = $file_info;
		unset($file_info);
		break;
	case $ACTIONS['download']:
		$path = trim($_GET['path']);
		empty($path) and die('path is empty');
                $path = preg_replace('/\/{2,}/','/',$path);
                $path = preg_replace('/^\/|\/$/','',$path);
		if(!storage_valid_path($path)) die("Invalid path..");
                $filename = storage_extend_path($path);
                if(is_dir($filename)) die('filename specified is a directory');
		$dirname = dirname($filename);
		$basename = basename($filename);
		
		#echo "begin download1\n";
		storage_do_download($path);
		#echo "begin download2\n";
		download($dirname,$basename);
		/*
		write_log("download over");
		ignore_user_abort (true);
		
		$path_dir = dirname($path);
		$info = storage_files_info($path_dir);
		write_log(var_export($info,true));
		if(isset($info[$basename]['md5']))
			exit;
		$md5 = md5_file($filename);
		write_log($md5);
		storage_set_file_info($path,'md5',$md5);
		//var_dump($filename);
		*/
		exit;
		break;
	case $ACTIONS['about']:
		$PAGE['title'] .= "About";
		$PAGE['show'] = 'about';
		break;
	case $ACTIONS['search']:
		$PAGE['title'] = 'Search';
		$PAGE['show'] = 'search';
		$search_res = array();
		$search_res['succeed'] = FALSE;
		if(!isset($_GET['keyword']) || empty(trim($_GET['keyword']))) {
			$search_res['errmsg'] = "Empty keyword is not allow!";
		} else {
			$keyword = mb_convert_encoding(trim($_GET['keyword']), 'UTF-8','GB2312,UTF-8');
			$search_res['keyword'] = $keyword;
			if(iconv_strlen($keyword,'UTF-8') < 2
				/*||stristr($keyword,'iso')*/) {
				$search_res['errmsg'] = "Key word is too short.";
			} else {
				$search_res['succeed'] = TRUE;
				$result_set = search('',explode(' ',$keyword));
				//$search_res['result_set'] = $result_set;
				foreach($result_set as $item) {
					$search_res['list'][] = array(
						'filename' => $item['filename'],
						'url' => $_INDEX.'?action='.$ACTIONS['file_info'].'&path='.$item['path'].'/'.$item['filename'],
						'comment' => $item['comment']
					);
				}
				unset($result_set);
				
			}
		}
		$PAGE['search_result'] = $search_res;
		unset($search_res);
		break;
	case $ACTIONS['guestbook']:
		$PAGE['show'] = 'guestbook';
                $PAGE['guestbook']['email'] = '';
                $PAGE['guestbook']['message'] = '';
                $PAGE['guestbook']['error_message'] = '';
		$start = isset($_GET['start'])?intval($_GET['start']):-1;
		//$for = isset($_GET['for'])?trim($_GET['for']) : null;
		$msg_set = guestbook_list($start,10);
		$PAGE['guestbook']['messages'] = $msg_set['list'];
		$PAGE['guestbook']['up_url'] 
			= !$msg_set['right_offset'] ? $_INDEX.'?action='.$ACTIONS['guestbook'].'&start='.strval($start + 10) : null;
		$PAGE['guestbook']['down_url'] 
			= !$msg_set['left_offset'] ? $_INDEX.'?action='.$ACTIONS['guestbook'].'&start='.strval($start - 10) : null;
		break;
	case $ACTIONS['leave_message']:
		$PAGE['guestbook'] = array();
		if(!isset($_POST['email']) || !isset($_POST['message'])) {
			die('Both Email and Message must be specified');
		}
		$email =  htmlspecialchars($_POST['email']);
		$message =  htmlspecialchars($_POST['message']);
		
		//$PAGE['guestbook']['email'] = "";
                //$PAGE['guestbook']['message'] = "";
                //$PAGE['guestbook']['error_message'] = "";
		if(strlen($message)<5) {
			$PAGE['messagebox']['text'] = 'Message is too short.';
		} elseif(guestbook_add($email,$message) == false) {
			$PAGE['messagebox']['text'] = guestbook_error_message();
		} else {
			header('Location:'.$_INDEX.'/?action='.$ACTIONS['guestbook'].'&start=-1');
			exit();
		}
		$PAGE['show'] = 'messagebox';
		//$msg_set = guestbook_list(-1,10);
		//if($msg_set == null) die ('Error: '.guestbook_error_message());
		//$PAGE['guestbook']['messages'] = $msg_set['list'];
		//$PAGE['guestbook']['up_url'] = null;
		//$PAGE['guestbook']['down_url'] = !$msg_set['left_end'] ? $_INDEX.'?action='.$ACTIONS['guestbook'].'&start='.'-11' : null;

		break;
	default:
		die('Unknow action.');
}
$THOME = dirname(__FILE__).'/templates/'.$CONFIG['template'];
//var_dump($THOME);
if(!is_dir($THOME))
	die('Template "'.$CONFIG['template'].'" not found.');
include($THOME.'/page.php');

?>
