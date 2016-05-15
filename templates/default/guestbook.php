<?php
$guestbook = & $PAGE['guestbook'];
//$msgs = & $PAGE['guestbook']['messages'];
?>
<p>
<form method="post" action="<?php echo $CONFIG['www_root_r'].'/?action='.$ACTIONS['leave_message']?>">
	<label for="email">Email*:</label>
	<input name="email" id="email" type="text" value="<?php echo $guestbook['email']?>"/><br />
	<textarea name="message" value="<?php echo $guestbook['message']?>"></textarea><br />
	<!--<input name="action" value="<?php echo $ACTIONS['guestbook']?>" type="hidden" />-->
	<input type="submit" value="Submit" />
</form>
</p>
<p>
<?php if(empty($guestbook['messages'])) { ?>
<p> Empty </p>
<?php } else { ?>
<table frame="above">
<?php foreach($guestbook['messages'] as $msg) { ?>
<tr>
	<th><?php echo $msg['id']?></th>
	<td><?php echo $msg['valid']?$msg['email']:'*'?></td>
	<td><?php echo $msg['date']?></td>
</tr>
</tr>
	<td colspan="3"><?php echo $msg['valid']?$msg['message']:'This message have been removed by administrator'?></td>
</tr>
<tr>
<td colspan="3"><hr /></td>
</tr>

<?php } ?>
</table>
<?php } ?>


<?php if(isset($PAGE['guestbook']['up_url'])) {?>
<a href="<?php echo $PAGE['guestbook']['up_url']?>">UP</a>
<?php } else { ?>
UP
<?php } ?>

<?php if(isset($PAGE['guestbook']['down_url'])) {?>
<a href="<?php echo $PAGE['guestbook']['down_url']?>">DOWN</a>
<?php } else { ?>
DOWN
<?php } ?>
</p>
