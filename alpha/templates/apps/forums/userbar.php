<?php
$words = new MOD_words();
?>

           <h3><?php echo $words->getFormatted('Actions'); ?></h3>
           <ul class="linklist">
<?php // TODO: Add new words to database 
?>
	        <li><a href="forums/index"><?php echo $words->getFormatted('ForumBackToIndex'); ?></a></li>
	        <li><a href="forums/new"><?php echo $words->getFormatted('ForumStartNewTopic'); ?></a></li>
	        <li></li>
	        <li><a href="forums/news"><?php echo $words->getFormatted('ForumNews'); ?></a></li>
	        <li><a href="forums/rules"><?php echo $words->getFormatted('ForumRulesAndGuidelines'); ?></a></li>					
           </ul>
		   
