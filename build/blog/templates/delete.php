<?php
$words = new MOD_words();
?>
<h2><?=$words->get('BlogDeleteTitle')?></h2>
<form method="post" action="blog/<?=$post->user_handle?>">
    <p>
        <input type="hidden" name="id" value="<?=$post->blog_id?>"/>
        <input type="hidden" name="<?=$callbackId?>" value="1"/>
        <input type="submit" name="n" value="<?=$words->get('No')?>"/>
        <input type="submit" name="y" value="<?=$words->get('Yes')?>"/>
    </p>
</form>