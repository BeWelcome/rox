<?php 
$comment_byloggedinmember = $this->member->get_comments_commenter($this->loggedInMember->id);
if ($this->loggedInMember && !$this->myself) { 
    if ($comment_byloggedinmember && $comment_byloggedinmember[0]) { ?>
  <p><a href="members/<?=$username?>/comments/add" class="button"><?=$words->get('editcomments')?></a></p>
  <? } else { ?>
  <p><a href="members/<?=$username?>/comments/add" class="button"><?=$words->get('addcomments')?></a></p>
  
<? } } ?>
<?php require_once 'comment_template.php' ?>