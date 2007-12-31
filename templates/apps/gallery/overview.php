<?php

$words = new MOD_words();

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
    $p = PFunctions::paginate($statement, $page);
    $statement = $p[0];
    foreach ($statement as $d) {
    	echo '
<div class="img thumb">
    <a href="gallery/show/image/'.$d->id.'"><img class="framed" src="gallery/thumbimg?id='.$d->id.'" alt="image"/></a>
    <h4><a href="gallery/show/image/'.$d->id.'">'.$d->title.'</a></h4>
    <p class="small">'.$d->width.'x'.$d->height.'; '.$d->mimetype.'; '.$words->getFormatted('GalleryUploadedBy').': <a href="bw/member.php?cid='.$d->user_handle.'">'.$d->user_handle.'</a>.</p>
        ';
        if ($User = APP_User::login()) {
        	echo '
    <p class="small"><a href="gallery/edit/image/'.$d->id.'">'.$words->getFormatted('GalleryEditImage').'</a></p>
            ';
        }
        echo '
</div>';
    }
    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    $request = $requestStr.'/page%d';
    require TEMPLATE_DIR.'misc/pages.php';
}
?>