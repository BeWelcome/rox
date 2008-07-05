<?php


class CommentsPage extends MemberPage
{
    protected function leftSidebar()
    {
    	$words = $this->getWords();
        $member = $this->member;
        ?>
          <h3><?=$words->get('Actions')?></h3>
          <ul class="linklist" >
            <li class="icon contactmember16" >
              <a href="contactmember.php?cid=<?=$member->id?>" ><?=$words->get('ContactMember');?></a>
            </li>
            <li class="icon addcomment16" >
              <a href="members/<?=$member->Username?>/comments/add" ><?=$words->get('addcomments');?></a>
            </li>
          </ul>
        <?php
    }
    
    
    protected function getSubmenuActiveItem()
    {
        return 'comments';
    }
    
    
    protected function column_col3()
    {
    	$comments = $this->member->comments;
    	foreach ($comments as $c) {
    		//echo "comment: ";
    		//print_r($c);
    	}
        require "templates/comments_main.php";
    }
}


?>