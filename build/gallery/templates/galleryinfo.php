<?php
$request = PRequest::get()->request;
$member = $this->model->getLoggedInMember();

$g = $gallery;
$g->user_handle = MOD_member::getUserHandle($g->user_id_foreign);

// Set variable own (if own gallery)
$Own = false;
if ($member) {
    //$callbackId = $Gallery->editGalleryProcess($gallery);
    //$vars =& PPostHandler::getVars($callbackId);
    $R = MOD_right::get();
    $GalleryRight = $R->hasRight('Gallery');
    $Own = ($member->Username == $g->user_handle) ? true : false;
}
if (!isset($vars['errors'])) {
    $vars['errors'] = array();
}
// $i18n = new MOD_i18n('date.php');
// $format = $i18n->getText('format');
$words = new MOD_words();

?>

<h2 id="g-title"><?=$g->title ?></h2>

<?php 
    if ($Own && !$g->text) echo '<p id="g-text">'.$words->get('GalleryAddDescription').'</p>';
    else echo '<p id="g-text">'.$g->text.'</p>';

    if ($Own) {
?>
<a href="gallery/show/sets/" id="g-title-edit" class="button"><?=$words->get('EditTitle'); ?></a>
<a href="gallery/show/sets/" id="g-text-edit" class="button"><?=$words->get('EditDescription'); ?></a><br />
        <script type="text/javascript">
        new Ajax.InPlaceEditor('g-title', 'gallery/ajax/set/', {
                callback: function(form, value) {
                    return '?item=<?=$g->id?>&title=' + decodeURIComponent(value)
                },
                externalControl: 'g-title-edit',
                formClassName: 'inplaceeditor-form-big',
                cols: '25',
                ajaxOptions: {method: 'get'}
            })

        new Ajax.InPlaceEditor('g-text', 'gallery/ajax/set/', {
                callback: function(form, value) {
                    return '?item=<?=$g->id?>&text=' + decodeURIComponent(value)
                },
                externalControl: 'g-text-edit',
                rows: '5',
                cols: '25',
                ajaxOptions: {method: 'get'}
            })
        </script>
<?php 
}

echo '<p class="small"> '.$cnt_pictures.' '.$words->getFormatted('GalleryImagesTotal').' </p>';
echo '
    <div class="floatbox" style="padding-top: 30px;">
        '.MOD_layoutbits::PIC_30_30($g->user_handle,'',$style='float_left').'
    <p class="small">'.$words->getFormatted('GalleryUploadedBy').': <a href="members/'.$g->user_handle.'">'.$g->user_handle.'</a>.<br /></p>
    <p class="small"><a href="gallery/show/user/'.$g->user_handle.'/sets">'.$words->getFormatted('GalleryAllGalleriesBy').' <img src="images/icons/images.png"></a></p>
    </div>
    ';

if ($Own || ($member && ($GalleryRight > 1)) ) {
    echo '
    <div class="floatbox" style="padding-top: 30px;">
    <p><a style="cursor:pointer" href="gallery/show/sets/'.$g->id.'/delete" class="button" onclick="return confirm(\''. $words->getSilent("confirmdeletegallery").'\')"><img src="images/icons/delete.png"> '.$words->getSilent("GalleryDelete").' </a></p>
    </div>';
    echo $words->flushBuffer();
}

?>
<p style="padding-top: 30px;"></p>

