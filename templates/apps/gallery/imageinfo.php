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
if (!isset($vars['errors'])) {
    $vars['errors'] = array();
}
$i18n = new MOD_i18n('date.php');
$format = $i18n->getText('format');
$words = new MOD_words();

$d = $image;
?>

<?php
echo '
    <div class="floatbox" style="padding-top: 30px;">
        '.MOD_layoutbits::PIC_30_30($d->user_handle,'',$style='float_left').'
    <p class="small">'.$words->getFormatted('GalleryUploadedBy').': <a href="bw/member.php?cid='.$d->user_handle.'">'.$d->user_handle.'</a>.</p>
    </div>';
    ?>

<?php 
if (!$d->description == 0) {echo '<p>'.$d->description.'</p>';}

if ($User && (($User->getId() == $d->user_id_foreign) || ($GalleryRight > 1)) ) {
    echo '<p class="small"><a style="cursor:pointer" href="gallery/show/image/'.$d->id.'/delete" class="button" onclick="return confirm(\''. $words->getFormatted("confirmdeletepicture").'\')"> Delete </a> <a style="cursor:pointer" class="button" onclick="$(\'image-edit\').toggle()"> Edit </a> </p>';
}

if ($User && $User->getId() == $d->user_id_foreign) {
?>
<form method="post" action="gallery/show/image/<?=$d->id?>/edit" class="def-form">
    <fieldset id="image-edit" class="inline" style="display:none;">
    <legend><?php echo $words->getFormatted('GalleryTitleEdit'); ?></legend>
    
        <div class="row">
            <label for="image-edit-t"><?php echo $words->getFormatted('GalleryLabelTitle'); ?></label><br/>
            <input type="text" id="image-edit-t" name="t" class="short"<?php
                echo ' value="'.htmlentities($d->title, ENT_COMPAT, 'utf-8').'"';
            ?>/><br/><br/>
            <label for="image-edit-txt"><?php echo $words->getFormatted('GalleryLabelText'); ?></label><br/>
            <textarea id="image-edit-txt" name="txt" cols="30" rows="4"><?php 
            echo htmlentities($d->description, ENT_COMPAT, 'utf-8'); 
            ?></textarea>
            <div id="bcomment-text" class="statbtn"></div>
	        <input type="hidden" name="<?php echo $callbackId; ?>" value="1"/>
	        <input type="hidden" name="id" value="<?=$d->id?>"/>
            <p class="desc"><?php echo $words->getFormatted('GalleryDescTitle'); ?></p>
            <input type="submit" name="button" value="submit" id="button" />
        </div>
        <div class="row">
        </div>    
</fieldset>
</form>
<?php 
}
?>

