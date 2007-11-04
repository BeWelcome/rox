<?php
$words = new MOD_words();
?>

           <h3>Actions</h3>
           <ul class="linklist">
<?php // TODO: Add new words to database 
?>
	        <li><a href="forums/index"><?php echo $words->getFormatted('ForumBackToIndex'); ?></a></li>
	        <li><a href="forums/new"><?php echo $words->getFormatted('ForumStartNewTopic'); ?></a></li>
					
           </ul>
		   