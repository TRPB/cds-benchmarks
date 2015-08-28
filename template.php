<ul>
	<?php
	foreach ($topics as $topic) :
	?>
	<li>
		<h2><?= $topic->title; ?></h2>
		<span><?= $topic->author; ?></span>
	</li>
	<?php endforeach; ?>
</ul>
