<?php
$User = APP_User::login();
$request = PRequest::get()->request;
$Gallery = new Gallery;
if ($User) {
    $callbackId = $Gallery->editProcess($image);
    $vars =& PPostHandler::getVars($callbackId);
    $callbackIdCom = $Gallery->commentProcess($image);
    $varsCom =& PPostHandler::getVars($callbackIdCom);
    $R = MOD_right::get();
    $GalleryRight = $R->hasRight('Gallery');
}
    $d = $image;
    $d->user_handle = MOD_member::getUsername($d->user_id_foreign);
if (!isset($vars['errors'])) {
    $vars['errors'] = array();
}
$i18n = new MOD_i18n('date.php');
$format = $i18n->getText('format');
$words = new MOD_words();

echo '
<h2 id="g-title">'.$d->title.'</h2>';
if ($User && $User->getId() == $d->user_id_foreign) {
?>
<script type="text/javascript">
new Ajax.InPlaceEditor('g-title', 'gallery/ajax/image/', {
        callback: function(form, value) {
            return '?item=<?=$d->id?>&title=' + decodeURIComponent(value)
        },
        ajaxOptions: {method: 'get'}
    })
</script>
<?php } 
    if (!$d->description == 0) {echo '<p id="g-text">'.$d->description.'</p>';}
    else {
        echo '<p id="g-text">'.$words->getBuffered("GalleryAddDescription").'</p>'.$words->flushBuffer();
    }
    if ($User && $User->getId() == $d->user_id_foreign) {
?>
        <script type="text/javascript">
        new Ajax.InPlaceEditor('g-text', 'gallery/ajax/image/', {
                callback: function(form, value) {
                    return '?item=<?=$d->id?>&text=' + decodeURIComponent(value)
                },
                ajaxOptions: {method: 'get'}
            })
        </script>
<?php } ?>
<div class="floatbox">
<div class="img">
<?php
echo '<a id="link_'.$d->id.'" href="gallery/img?id='.$d->id.'" title="'.$d->title.' :: '.$d->description.'" class="lightview" rel="image">
    <img id="thumb_'.$d->id.'" src="gallery/thumbimg?id='.$d->id.'&amp;t=2" class="framed big" alt="image"/>
</a>';
?>
</div>
</div>

<div id="comments" style="padding: 10px 0px">
    <h3><?php echo $words->getFormatted('CommentsTitle'); ?></h3>
    
<?php
$comments = $Gallery->getComments($image->id);
if (!$comments) {
	echo '<p>'.$words->getFormatted('NoComments').'</p>';
} else {
    $count = 0;
    $lastHandle = '';
    foreach ($comments as $comment) {
        require TEMPLATE_DIR.'apps/gallery/comment.php';
        ++$count;
        $lastHandle = $comment->user_handle;
    }
}
?>

<h3><?php echo $words->getFormatted('CommentsAdd'); ?></h3>

<?php
if ($User) {
?>
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
<?
} else {
    // not logged in.
    echo '<p>'. $words->getFormatted('PleaseRegister') .'</p>';
}
?>
</div>
<?php
if ($User) { 
PPostHandler::clearVars($callbackId); 
PPostHandler::clearVars($callbackIdCom); 
}
?>