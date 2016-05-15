<?php $INFO = $PAGE['fileinfo'] ?>
<p>
<dl>
<dt>Filename:</dt>
<dd><?php echo $INFO['filename'] ?></dd>
<dt>Size:</dt>
<dd><?php echo $INFO['size'] ?></dd>
<dt>MD5:</dt>
<dd><?php echo $INFO['md5'] ?></dd>
<dt>Dowloads:</dt>
<dd><?php echo $INFO['downloads'] ?></dd>
<dt>Last download:</dt>
<dd><?php echo $INFO['last_time'] ?></dd>
<dt>Last 7 days:</dt>
<dd><?php echo implode(' ',$INFO['downloads_week']) ?></dd>
<dt>Upload time:</dt>
<dd><?php echo $INFO['create_time'] ?></dd>
<dt>Comment:</dt>
<dd><?php echo $INFO['comment'] ?></dd>
</dl>
<a href="<?php echo $INFO['url'] ?>">Download</a>.
</p>
<?php unset($INFO) ?>
