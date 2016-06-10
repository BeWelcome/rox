<?php
$words = new MOD_words($this->getSession());
$Gallery = new GalleryModel;

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
    echo '<div class="clearfix">';
    foreach ($galleriesonpage as $g) {
    	static $ii = 0;
        $d = $Gallery->getLatestGalleryItem($g->id);
        $s = $Gallery->getGalleryItems($g->id,1);
        $username = MOD_member::getUserHandle($g->user_id_foreign);
        $this->myself = ($this->loggedInMember && $username == $this->loggedInMember->Username);
        $num_rows = $s ? $s : 0;
        // Only show the galleries with pictures. The belonging user might see them anyway.
    	if ($d || $this->myself) {
    	?>
        <div class="gallery_container float_left">
            <a href="gallery/show/sets/<?=$g->id?>">
                <img class="framed" src="<?=($d) ? 'gallery/thumbimg?id='.$d : 'images/lightview/blank.gif'?>" alt="image"/>
            </a>
            <h4><a href="gallery/show/sets/<?=$g->id?>"><?= htmlspecialchars($g->title)?></a></h4>
            <p>
            <?=$num_rows?> <?=$words->get('pictures')?>
            <span class="grey small"><?=$words->get('by')?> <a href="members/<?=$username?>" class="grey"><?=$username?></a></span>
            </p>
        </div>
        <?php
        }
    }
    echo '</div>';
    if (isset($emptyPhotosets)) echo $emptyPhotosets;
    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    if (isset($requestStrNew)) $requestStr = $requestStrNew;
    $request = $requestStr.'/=page%d';
    require 'pages.php';
}
