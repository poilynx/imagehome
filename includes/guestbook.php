<?php
require_once('config.php');
require_once('utils.php');

static $m_errmsg = "";

function size_to_count($size) {
	$mod = $size % 500;
	$count = intval($size / 500) + ($mod > 0 ? 1 : 0);
	return $count;
}
function guestbook_add($email,$message) {
	global $m_errmsg;
	global $CONFIG;
	$email = trim($email);
	if(empty($email))
		$email = 'anonymous';
	elseif(!is_email($email)) {
		$m_errmsg = "Unrecognisable email address.";
		return false;
	}
	$fstate = fopen($CONFIG['guestbook_root'].'/state','r+') or die ("Open guestbook->state error.");
	
	flock($fstate,LOCK_SH);
	$state = stream_get_contents($fstate);
	$state = object2array(json_decode($state));
	if(!isset($state['cur_id'])) //die("'cur_id' not found in guetbook->state");
		$state['cur_id'] = 0;
	$data = array(
		'i' => intval($state['cur_id']) + 1,
		'e' => $email,
		'm' => $message,
		'd' => date('F d Y',time())
	);
	$data = json_encode($data);
	if(strlen($data)>500) {
		fclose($fstate);
		$m_errmsg = "Message size can not be larger then 500";
		return false;
	}
	$fmsg = fopen($CONFIG['guestbook_root'].'/messages','a');
	if(!$fmsg) {
		fclose($fstate);
		die("Open guestbook->messages error.");
	}
	
	flock($fmsg,LOCK_EX);
	$stat = fstat($fmsg);
	$count = size_to_count($stat['size']);
	//echo $count;
	ftruncate($fmsg,$count*500);
	fseek($fmsg,$count*500,SEEK_SET);
	fwrite($fmsg,$data);
	flock($fmsg,LOCK_UN);

	flock($fstate,LOCK_EX);
	$state['cur_id'] ++;
	$state = json_encode($state);
	ftruncate($fstate,0);
	fseek($fstate,0);
	fwrite($fstate,$state);
	flock($fstate,LOCK_UN);
	
	fclose($fmsg);
	fclose($fstate);
	return true;
}

function guestbook_delete($id) {
	global $CONFIG;

}

function guestbook_list($start,$length) {
	global $CONFIG;
	global $m_errmsg;
	//$start + = $length;//Reserve scanning direction;
	$fmsg = fopen($CONFIG['guestbook_root'].'/messages','r');
	if(!$fmsg) {
		fclose($fstate);
		die("Open guestbook->messages error.");
	}
	flock($fmsg,LOCK_SH);
	$stat = fstat($fmsg);
	$count = size_to_count($stat['size']);
	if($count == 0) {
		return array('list'=>array(),'count'=>0,'left_offset'=>true,'right_offset'=>true);
	}
	if($start < $count*-1 || $length < 1 || $start >= $count) {
		/* error: ranger is invalid */
		$m_errmsg = "Ranger is invalid";
		return false;
	}
	$nreal = $length;
	//$left_end = false;
	//$right_end = false;
	$left_offset = 0;
	$right_offset = 0;
	//if($start == 0) $left_end = true;
	//if($start == -1) $right_end = true;

	//$nreal = $count + $start + 1;
	$list = array();
	if($start >= 0) {
		$right_offset = $start + $length > $count - 1 ? true : false;
		$left_offset = $start - $length  < 0 ? true :  false;
		$begin = $start;
		$end = $start + $nreal - 1;
		$end = $start + $length <= $count ? $length - 1 : $count -1;
	} else {
		$left_offset = $start - $length  < $count * -1 ? true : false;
		$right_offset = $start + $length > -1 ? true : false;
		$begin = $count + $start;
		$end = $count + $start + 1 >= $length ? $count + $start + 1 - $length : 0;//Ma mai ma pi!!
	}
	/*
	if($start >= 0) {
		$right_offset = $start + $length > $count - 1 ? $count - $start :  $length;
		$left_offset = $start - $length  < 0 ? $start :  $length;
		$begin = $start;
		$end = $start + $nreal - 1;
		$end = $start + $length <= $count ? $length - 1 : $count -1;
	} else {
		$left_offset = $start - $length  < $count * -1 ? $start + $count :  $length;
		echo $start." ".$length." ".$count."\n";
		$right_offset = $start + $length > -1 ? $start * -1 - 1 : $length;
		$begin = $count + $start;
		$end = $count + $start + 1 >= $length ? $count + $start + 1 - $length : 0;//Ma mai ma pi!!
	}*/
	/*
	if($start >= 0) {
		$right_offset = $start + $length > $count - 1 ? 0 : $start + $length;
		$left_offset = $start - $length  < 0 ? 0 : $start - $length;
		$begin = $start;
		$end = $start + $nreal - 1;
		$end = $start + $length <= $count ? $length - 1 : $count -1;
	} else {
		$left_offset = $start - $length  < $count * -1 ? 0 : $start - $length;
		$right_offset = $start + $length > -1 ? 0 : $start + $length;
		$begin = $count + $start;
		$end = $count + $start + 1 >= $length ? $count + $start + 1 - $length : 0;//Ma mai ma pi!!
	}*/
	for($i = $begin; $begin <= $end ? $i <= $end : $i >= $end ; $begin <= $end ? $i++ : $i--) {
		fseek($fmsg,500 * $i);
		$message = rtrim(fread($fmsg,500));
		$message = json_decode($message);
		$message = object2array($message);
		if(!$message)
			$list[] = array('valid'=>false);
		else
			$list[] = array(
				'valid'=>true,
				'id'=>@$message['i'],
				'email'=>@$message['e'],
				'message'=>@$message['m'],
				'date'=>@$message['d']
			);
			
	}
	flock($fmsg,LOCK_UN);
	fclose($fmsg);
	return array('list'=>$list,'count'=>$count,'left_offset'=>$left_offset,'right_offset'=>$right_offset);
}

function guestbook_tail($length) {

}

function guestbook_at($id) {
	global $CONFIG;

}

function guestbook_error_message() {
	global $m_errmsg;
	return $m_errmsg;
}
?>
