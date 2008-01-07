<?php
$words = new MOD_words();
?>

           <h3><?php echo $words->getFormatted('Actions'); ?></h3>
           <ul class="linklist">
	        <li class="icon fam_commentadd"><a href="forums/new"><?php echo $words->getBuffered('ForumNewTopic'); ?></a><?php echo $words->flushBuffer(); ?></li>
	        <li><a href="forums/news"><?php echo $words->getFormatted('ForumNews'); ?></a></li>
           </ul>

        <div class="floatbox">
           <h3><?php echo $words->get('ForumRulesShort'); ?></h3>
	          
          <div class="small">
          
              <p><?php echo $words->getFormatted('ForumRulesShortIntro','<a href="forums/rules">','</a>'); ?>:</p>
              <dl class="sidebarInfoList">
              
                <dt><?php echo $words->get('ForumRulesShort1'); ?></dt>
                    <dd><?php echo $words->get('ForumRulesShort1Text'); ?></dd>
                <dt><?php echo $words->get('ForumRulesShort2'); ?></dt>
                    <dd><?php echo $words->getFormatted('ForumRulesShort2Text'); ?></dd>
                <dt><?php echo $words->get('ForumRulesShort3'); ?></dt>
                    <dd><?php echo $words->get('ForumRulesShort3Text'); ?></dd>      
                <dt><?php echo $words->get('ForumRulesShort4'); ?></dt>
                    <dd><?php echo $words->get('ForumRulesShort4Text'); ?></dd>      
                    
              </dl>
                 
          </div>
        
    	   <p class="chartmore"><a href="forums/rules">Read more...</a></p>
          <span class="iesucks">&nbsp;</span>
        </div> 
        
