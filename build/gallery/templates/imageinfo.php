<?php
$words = new MOD_words();
$request = PRequest::get()->request;
$Gallery = new GalleryModel;
if ($Gallery->getLoggedInMember()) {
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

$d = $image;
?>

<?php
echo '
    <div class="floatbox" style="padding-top: 30px;">
        '.MOD_layoutbits::PIC_30_30($d->user_handle,'',$style='float_left').'
    <p class="small">'.$words->getFormatted('GalleryUploadedBy').': <a href="members/'.$d->user_handle.'">'.$d->user_handle.'</a>.</p>
    </div>';
    ?>

