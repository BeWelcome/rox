<?php

$words = new MOD_words();
$User = new APP_User;
if ($statement) {
    $request = PRequest::get()->request;
    $requestStr = implode('/', $request);
    $matches = array();
    if (preg_match('%/page(\d+)%', $requestStr, $matches)) {
        $page = $matches[1];
        $requestStr = preg_replace('%/page(\d+)%', '', $requestStr);
    } else {
        $page = 1;
    }
    $p = PFunctions::paginate($statement, $page, $itemsPerPage = 10);
    $statement = $p[0];
    echo '<div class="floatbox">';
    foreach ($statement as $d) {
    	echo '
<div class="img thumb float_left" style="width: 160px; margin-bottom: 30px;">
    <a href="gallery/show/image/'.$d->id.'"><img class="framed" src="gallery/thumbimg?id='.$d->id.'" alt="image" style="margin: 5px 0; float:none;" /></a>
    <h4><a href="gallery/show/image/'.$d->id.'">'.$d->title.'</a></h4>
    <p class="small">'.$d->width.'x'.$d->height.'; '.$d->mimetype.'; '.$words->getFormatted('GalleryUploadedBy').': <a href="bw/member.php?cid='.$d->user_handle.'">'.$d->user_handle.'</a>.</p>
        ';
if ($User && $User->getHandle() == $d->user_handle) {
    echo '
    <p class="small"><a href="gallery/edit/image/'.$d->id.'"><img src="styles/YAML/images/iconsfam/picture_edit.png"></a></p>
    <p class="small"><a href="gallery/show/image/'.$d->id.'/delete" onclick="return confirm(\''. $words->getFormatted("confirmdeletepicture").'\')"><img src="styles/YAML/images/iconsfam/delete.png"></a></p>';
echo '    <input type="checkbox" name="imageId[]" value="'.$d->id.'">';
}
echo '<p class="small"><a href="gallery/img?id='.$d->id.'" class=\'lightview\' rel=\'gallery[BestOf]\'><img src="styles/YAML/images/iconsfam/pictures.png"></a></p></div>';
    }
    echo '</div>';
    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    $request = $requestStr.'/page%d';
    require TEMPLATE_DIR.'misc/pages.php';
}
?>