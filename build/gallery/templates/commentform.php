<h3><?php echo $words->getFormatted('CommentsAdd'); ?></h3>

<form method="post" action="gallery/show/image/<?=$d->id?>/comment" class="def-form" id="gallery-comment-form">
    <div class="row">
    <label for="comment-title"><?php echo $words->getFormatted('CommentsLabel'); ?>:</label><br/>
        <input type="text" id="comment-title" name="ctit" class="long" <?php 
echo isset($varsCom['ctit']) ? 'value="'.htmlentities($varsCom['ctit'], ENT_COMPAT, 'utf-8').'" ' : ''; 
?>/>
        <div id="bcomment-title" class="statbtn"></div>
<?
if (in_array('title', $vars['errors'])) {
    echo '<span class="error">'.$commentsError['title'].'</span>';
}
?>
        <p class="desc"></p>
    </div>
    <div class="row">
        <label for="comment-text"><?php echo $words->getFormatted('CommentsTextLabel'); ?>:</label><br />
        <textarea id="comment-text" name="ctxt" cols="40" rows="5"><?php 
echo isset($varsCom['ctxt']) ? htmlentities($varsCom['ctxt'], ENT_COMPAT, 'utf-8') : ''; 
      ?></textarea>
        <div id="bcomment-text" class="statbtn"></div>
<?
if (in_array('textlen', $vars['errors'])) {
    echo '<span class="error">'.$commentsError['textlen'].'</span>';
}
?>
        <p class="desc"><?php echo $words->getFormatted('CommentsSublineText'); ?></p>
    </div>
    <p>
        <input type="submit" value="<?php echo $words->getFormatted('SubmitForm'); ?>" class="submit" />
        <input type="hidden" name="<?php
// IMPORTANT: callback ID for post data 
echo $callbackIdCom; ?>" value="1"/>
    </p>
</form>