<?php require_once 'comment_template.php' ?>
<?php if (!$this->myself) { ?>
  <a href="members/<?=$username?>/comments/add" class="button"><?=$words->get('addcomments')?></a>
<? } ?>
