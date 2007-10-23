<?php
$User = APP_User::login();
if ($User) {
    $Gallery = new Gallery;
    $callbackId = $Gallery->editProcess();
    $vars =& PPostHandler::getVars($callbackId);
}
$imgText = array();
$i18n = new MOD_i18n('apps/gallery/image.php');
$imgText = $i18n->getText('imgText');

$d = $image;
echo '
<h2>'.$d->title.'</h2>
<div class="img">
    <img src="gallery/thumbimg?id='.$d->id.'&amp;t=2" class="framed" alt="image"/>
    <p class="small">'.$d->width.'x'.$d->height.'; '.$d->mimetype.'; '.$imgText['uploaded_by'].': <a href="bw/member.php?cid='.$d->user_handle.'">'.$d->user_handle.'</a>.</p>';

echo '<p class="small"><a href="gallery/img?id='.$d->id.'&amp;s=1"><img src="images/icons/disk.png" alt="'.$imgText['download'].'" title="'.$imgText['download'].'"/></a></p>';
if ($User && $User->getId() == $d->user_id_foreign) {
    echo '<p class="small"><a href="gallery/show/image/'.$d->id.'/delete"><img src="images/icons/delete.png" alt="'.$imgText['delete'].'" title="'.$imgText['delete'].'"/></a></p>';
}

if ($User && $User->getId() == $d->user_id_foreign) {
?>
<form method="post" action="gallery/show/image/<?=$d->id?>" class="def-form">
    <fieldset id="image-edit" class="inline">
    <legend><?=$imgText['title_edit']?></legend>
    <h3><?=$imgText['title_edit']?></h3>
    
        <div class="row">
            <label for="image-edit-t"><?=$imgText['label_title']?></label><br/>
            <input type="text" id="image-edit-t" name="t" class="long"<?php
if (!isset($vars['t'])) {
	echo ' value="'.htmlentities($d->title, ENT_COMPAT, 'utf-8').'"';
}
        ?>/>
            <p class="desc"><?=$imgText['desc_title']?></p>
        </div>
        <div class="row">
        </div>    
</fieldset>
</form>
<script type="text/javascript">//<!--
createFieldsetMenu();
//-->
</script>
<?php
}
?>