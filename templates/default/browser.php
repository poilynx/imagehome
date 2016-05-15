<?php $BROWSER = $PAGE['browser']; ?>
<p>
	<?php foreach($BROWSER['path'] as $item) {?>
	/<a href="<?php echo $item['url']?>"><?php echo $item['text']?></a>
	<?php }?>
</p>
<p>
<table>
	<?php if($BROWSER['show']==1) {?>
	<th></th>
	<th>Name</th>
	<th>Comment</th>
	<tr><th colspan="3"><hr></th></tr>
	<?php foreach($BROWSER['items'] as $item) { ?>
	<tr>
		<td>
			<img height="16" width="16" alt="DIR" src="<?php echo $CONFIG['www_root_r']?>/templates/default/images/folder.png" />
		</td>
		<td>
			<a href="<?php echo $item['url']?>"><?php echo $item['text'] ?></a>
		</td>
		<td><?php echo $item['comment'] ?></td>
	</tr>
	<?php } ?>
	<tr><th colspan="3"><hr></th></tr>
	<?php }else{ ?>
	<th></th>
	<th>Name</th>
	<th>Size</th>
	<th>Downloads</th>
	<th>Discription</th>
	<tr><th colspan="5"><hr></th></tr>
	<?php foreach($BROWSER['items'] as $item) {
		/*
                $suf = end(explode('.', $item['text']));
                switch(strtolower($suf)){
                        case 'iso':
                                $filename = 'file_iso.png';
                                break;
                        case 'zip':
                        case 'tar':
                        case 'tar.gz':
                        case 'tar.bz2':
                        case 'zip':
                        case 'rar':
                        case '7z':
                        case 'xz':
                                $filename = 'file_zip.png';
                                break;
                        default:
                                $filename = 'file_default.png';
                }
                $icon_url = '/'.$CONFIG['www_root_r'].'/templates/default/images/'.$filename;
		*/
		$icon_url = get_icon_url_by_filename($item['text']);
	?>
	<tr>
		<td>
			<img width="16" height="16" alt="FILE" src="<?php echo $icon_url?>" />
		</td>
		<td>
			<a href="<?php echo $item['url']?>"><?php echo $item['text'] ?></a>
		</td>
		<td><?php echo $item['size'] ?></td>
		<td><?php echo $item['downloads'] ?></td>
		<td><?php echo $item['comment'] ?></td>
	</tr>
	<?php } ?>
	<tr><th colspan="5"><hr></th></tr>
	<?php } ?>
</table>
<p>
<?php unset($BROWSER) ?>
