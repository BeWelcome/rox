<?php if (!$this->myself) { ?>
  <a href="members/<?=$username?>/comments/add" class="button float_right"><?=$words->get('addcomments')?></a>
<? } ?>

<?php require_once 'comment_template.php' ?>
