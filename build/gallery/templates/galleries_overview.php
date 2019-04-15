<div class="row">
<?php
$words = new MOD_words();
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
    if (!isset($itemsPerPage)) $itemsPerPage = 12;
    $p = PFunctions::paginate($galleries, $page, $itemsPerPage);
    $galleriesonpage = $p[0];

    ?>
        <div class="col-12 col-sm-6 col-md-3 text-center">
            <a href="members/<?= $username ?>">
                <img class="framed w-100" src="members/avatar/<?= $username ?>/100" alt="Picture of <?= $username ?>" width="100%" alt="Profile of <?= $username ?>">
                <span class="w-100"><?=$username?></span>
            </a>
        </div>

    <? foreach ($galleriesonpage as $g) {
    	static $ii = 0;
        $d = $Gallery->getLatestGalleryItem($g->id);
        $s = $Gallery->getGalleryItems($g->id,1);
        $username = MOD_member::getUserHandle($g->user_id_foreign);
        $this->myself = ($this->loggedInMember && $username == $this->loggedInMember->Username);
        $num_rows = $s ? $s : 0;
        // Only show the galleries with pictures. The belonging user might see them anyway.
    	if ($d || $this->myself) {
    	?>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="gallery_container">
                <a href="gallery/show/sets/<?=$g->id?>" data-toggle="lightbox" data-type="image">
                    <img class="mb-3 framed" src="<?=($d) ? 'gallery/thumbimg?id='.$d : 'images/lightview/blank.gif'?>" alt="image"/>
                    <span class="alert alert-info p-1" style="position: absolute;"><i class="fa fa-image mr-1"></i><?=$num_rows?></span>
                </a>
            <h4 class="mb-0"><a href="gallery/show/sets/<?=$g->id?>"><?= htmlspecialchars($g->title)?></a></h4>
            </div>
        </div>
        <?php
        }
    }
?>
    <div class="w-100"></div>
    <div class="col-12 mt-3">
        <?
    if (isset($emptyPhotosets)) echo $emptyPhotosets;
    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    if (isset($requestStrNew)) $requestStr = $requestStrNew;
    $request = $requestStr.'/=page%d';
    require 'pages.php'; ?>
    </div>
<? } ?>
</div>
