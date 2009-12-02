<?php
$words = new MOD_words();
$request = PRequest::get()->request;
$layoutbits = new MOD_layoutbits();
$d = $image = $this->image;

$Gallery = new GalleryModel;
$gallery_ctrl = new GalleryController;
if ($this->model->getLoggedInMember())
{
    $callbackId = $gallery_ctrl->editProcess($image);
    $vars =& PPostHandler::getVars($callbackId);
    $callbackIdCom = $gallery_ctrl->commentProcess($image);
    $varsCom =& PPostHandler::getVars($callbackIdCom);
    $R = MOD_right::get();
    $GalleryRight = $R->hasRight('Gallery');
}
if (!isset($vars['errors'])) {
    $vars['errors'] = array();
}

$Previous = $this->previous;
$Next = $this->next;
$userpic = MOD_layoutbits::PIC_30_30($d->user_handle,'',$style='float_left');
echo <<<HTML
    <div class="floatbox" style="padding-top: 30px;">
        {$userpic}
        <h3><a href="gallery/show/user/{$image->user_handle}">{$words->getFormatted('galleryUserOthers',$image->user_handle)}</a></h3>
    </div>
HTML;
$UserId = 1;
$SetId = false;

require_once 'surrounditems_small.php';

if ($this->gallery) {
    echo '<div class="floatbox" style="padding-top: 30px;"><h3 class="borderless">'.$words->getFormatted('Belongs to album').'</h3>';
    echo '<a href="gallery/show/sets/'.$this->gallery->id.'">'.$this->gallery->title.'</a>
    </div>';
}
$d = $image;
echo '    <div class="floatbox" style="padding-top: 30px;">
<h3 class="borderless">'.$words->getFormatted('GalleryImageAdditionalInfo').'</h3>';
echo '
    <p class="small" title="'.$d->created.'">'.$words->get('created').': '.$layoutbits->ago(strtotime($d->created)).'</p>
    <p class="small"><a href="gallery/img?id='.$d->id.'&amp;t=1" title="'.$words->getFormatted('GalleryOriginal').'" />'.$d->width.'x'.$d->height.'</a>; '.$d->mimetype.'</p>
    <p class="small"><a href="gallery/img?id='.$d->id.'&amp;s=1"><img src="images/icons/disk.png" alt="'.$words->getFormatted('GalleryDownload').'" title="'.$words->getFormatted('GalleryDownload').'"/> </a>  </a></p>
    </div>';
