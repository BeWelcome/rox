<?php
$deleteText = array();
$i18n = new MOD_i18n('apps/blog/delete.php');
$deleteText = $i18n->getText('deleteText');
?>
<h2><?=$deleteText['title']?></h2>
<form method="post" action="blog/<?=$post->user_handle?>">
    <p>
        <input type="hidden" name="id" value="<?=$post->blog_id?>"/>
        <input type="hidden" name="<?=$callbackId?>" value="1"/>
        <input type="submit" name="n" value="<?=$deleteText['submit_no']?>"/>
        <input type="submit" name="y" value="<?=$deleteText['submit_yes']?>"/>
    </p>
</form>