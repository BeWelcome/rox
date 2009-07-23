<?php
$words = new MOD_words();
$User = APP_User::login();
$request = PRequest::get()->request;
$layoutbits = new MOD_layoutbits();
$d = $image = $this->image;

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

$Previous = $this->previous;
$Next = $this->next;
echo '
    <div class="floatbox" style="padding-top: 30px;">
        '.MOD_layoutbits::PIC_30_30($d->user_handle,'',$style='float_left').'
    <p class="small">'.$words->getFormatted('GalleryUploadedBy').': <a href="bw/member.php?cid='.$d->user_handle.'">'.$d->user_handle.'</a>.</p>
    </div>';
$UserId = 1;
$SetId = false;

require_once 'surrounditems_small.php';

$d = $image;
    ?>
    
    <h3 class="borderless"><?php echo $words->getFormatted('GalleryImageAdditionalInfo'); ?></h3>
    <?php

    echo '
    <p class="small" title="'.$d->created.'">'.$words->get('created').': '.$layoutbits->ago(strtotime($d->created)).'</p>
    <p class="small">'.$d->width.'x'.$d->height.'; '.$d->mimetype.'</p>
    <p class="small"><a href="gallery/img?id='.$d->id.'&amp;s=1"><img src="images/icons/disk.png" alt="'.$words->getFormatted('GalleryDownload').'" title="'.$words->getFormatted('GalleryDownload').'"/> </a> </p>';
    ?>


