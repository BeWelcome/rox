<?php if (!$this->myself) { ?>
  <a href="members/<?=$username?>/comments/add" class="button float_right">Add comment</a>
<? } ?>
  <h3>Comments for <?=$username?></h3>
  
<?php require 'comment_template.php' ?>