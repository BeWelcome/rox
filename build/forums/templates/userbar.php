<?php
$words = new MOD_words();
?>

           <h3><?php echo $words->getFormatted('Actions'); ?></h3>
           <ul class="linklist">
					 <?php 
//	        echo "<li class=\"icon fam_commentadd\"><a href=\"forums/new\"" ;
        $request = PRequest::get()->request;
        $uri = implode('/', $request);
        $uri = rtrim($uri, '/').'/';

	        echo "<li class=\"icon fam_commentadd\"><a href=\"",$uri,"new\"" ;
					echo ">" ;
					echo $words->getBuffered('ForumNewTopic'); 
					?></a><?php echo $words->flushBuffer(); ?></li>
	        <li><a href="forums/rules"><?php echo $words->get('ForumRulesShort'); ?></a></li>
	        <li><a href="http://www.bevolunteer.org/wiki/forumdoc"><?php echo $words->get('ForumLinkToDoc'); ?></a></li>
	        <?php  if (isset($_SESSION["IdMember"])) echo "<li><a href=\"forums/subscriptions\">",$words->get('forum_YourSubscription'),"</a></li>"; ?>
           </ul>

