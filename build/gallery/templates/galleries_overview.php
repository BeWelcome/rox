<?php
$words = new MOD_words();
$Gallery = new Gallery;
$User = new APP_User;

// Show the galleries/photosets
if ($galleries) {
    $request = PRequest::get()->request;
    $requestStr = implode('/', $request);
    $matches = array();
    if (preg_match('%/=page(\d+)%', $requestStr, $matches)) {
        $page = $matches[1];
        $requestStr = preg_replace('%/=page(\d+)%', '', $requestStr);
    } else {
        $page = 1;
    }
    if (!isset($itemsPerPage)) $itemsPerPage = 6;
    $p = PFunctions::paginate($galleries, $page, $itemsPerPage);
    $galleriesonpage = $p[0];
    echo '<div class="floatbox">';
    foreach ($galleriesonpage as $g) {
    	static $ii = 0;
        $d = $Gallery->getLatestGalleryItem($g->id);
        $s = $Gallery->getGalleryItems($g->id,1);
        $member = MOD_member::getUsername($g->user_id_foreign);
        $num_rows = $s ;
        // Only show the galleries with pictures. The belonging user might see them anyway.
    	if ($d) {
            echo '<div class="gallery_container float_left" style="margin: 10px; height: 170px; width: 150px; padding: 20px; text-align: center;">
                <a href="gallery/show/sets/'.$g->id.'"><img class="framed" src="gallery/thumbimg?id='.$d.'" style="float:none; padding: 2px; padding-right: 10px; padding-bottom: 8px; background: #fff url(images/photoset_bg.png) bottom right no-repeat; border:2px solid #e5e5e5; border-bottom:0; border-right:0" alt="image"/></a>
                ';
            echo '<h4><a href="gallery/show/sets/'.$g->id.'">'.$g->title.'</a></h4>';
            //echo '<p class="small">';
            //if ($g->text) echo $g->text.'</p>';
            echo '<p class="small">'.$words->get('by').' '.$member.'</p>';
            echo '<p>'.$num_rows.' '.$words->get('pictures').'</p>';
            echo '</div>';
        } else {
            if ($User->getId() == $g->user_id_foreign) {
            if (!isset($emptyPhotosets)) $emptyPhotosets = '<h3>'.$words->get('GalleryEmptyPhotosets').'</h3>';
            $emptyPhotosets .= '<div class="gallery_container" style="margin: 10px; padding: 5px 0 0 5px;) no-repeat;">';
            $emptyPhotosets .= '<h4><a href="gallery/show/sets/'.$g->id.'">'.$g->title.'</a></h4>
            <p class="small">'.$g->text.'</p>';
            $emptyPhotosets .= '<img src="images/icons/picture_add.png"> <a href="gallery/show/sets/'.$g->id.'"> '.$words->get('GalleryPhotosetAddPictures').'</a>';
            $emptyPhotosets .= '</div>';
            }
        }
    }
    echo '</div>';
    if (isset($emptyPhotosets)) echo $emptyPhotosets;
    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    if (isset($requestStrNew)) $requestStr = $requestStrNew;
    $request = $requestStr.'/=page%d';
    require TEMPLATE_DIR.'misc/pages.php';
}
?>
