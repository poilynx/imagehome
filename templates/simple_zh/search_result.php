<?php
$search_res = $PAGE['search_result'];
?>
<?php if($search_res['succeed']) { ?>
	<?php if(!empty($search_res['list'])) { ?>
	<p>Total <?php echo count($search_res['list']);?> Results.</p>
	<table>
		<th></th>
		<th>File</th>
		<th>Comment</th>
		<tr><th colspan="3"><hr></th></tr>
		<?php foreach($search_res['list'] as $item) { ?>
		<tr>
		<td><img width="16" height="16" src="<?php echo get_icon_url_by_filename($item['filename'])?>" /></td>
		<td><a href="<?php echo $item['url']?>"><?php echo $item['filename']?></a></td>
		<td><?php echo $item['comment']?></td>
		</tr>
		<?php } ?>
	</table>
	<?php } else { ?>
	<p>No image found, please type other keyword and try again.</p>
	<?php } ?>
<?php } else { ?>
<p>Error: <?php echo $search_res['errmsg']?></p>
<?php } ?>
