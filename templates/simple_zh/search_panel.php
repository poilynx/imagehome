<p>
<form method="get" action="<?php echo $CONFIG['www_root_r'].'/'?>">
	<label for="keyword" >Keyword: </label>
	<input type="hidden" name="action" value="search" />
	<input name="keyword" id="keyword" type="text" value="<?php echo @$PAGE['search_result']['keyword']?>" />
	<input type="submit" value="Search" />
</form>
</p>
