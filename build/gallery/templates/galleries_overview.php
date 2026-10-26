<?php
$words = new MOD_words();
$Gallery = new GalleryModel;

if ($galleries) {
    $request = PRequest::get()->request;
    $requestStr = implode('/', $request);
    $matches = [];
    if (preg_match('%/=page(\d+)%', $requestStr, $matches)) {
        $page = $matches[1];
        $requestStr = preg_replace('%/=page(\d+)%', '', $requestStr);
    } else {
        $page = 1;
    }
    if (!isset($itemsPerPage)) $itemsPerPage = 24;
    $p = PFunctions::paginate($galleries, $page, $itemsPerPage);
    $galleriesonpage = $p[0];
?>
<div class="p-gallery-albums">
    <?php foreach ($galleriesonpage as $g) {
        $d = $Gallery->getLatestGalleryItem($g->id);
        $s = $Gallery->getGalleryItems($g->id, 1);
        $username = MOD_member::getUserHandle($g->user_id_foreign);
        $isOwn = ($this->loggedInMember && $username == $this->loggedInMember->Username);
        $num_rows = $s ?: 0;
        $albumHref = (!empty($inManage) && $isOwn)
            ? 'gallery/manage?set=' . $g->id
            : 'gallery/show/sets/' . $g->id;
        if ($d || $isOwn) { ?>
    <a href="<?= $albumHref ?>" class="p-gallery-album-card">
        <?php if ($d): ?>
            <img class="p-gallery-album-card__thumb"
                 src="gallery/thumbimg?id=<?= $d ?>&t=2"
                 alt="<?= htmlspecialchars((string) $g->title) ?>"
                 loading="lazy">
        <?php else: ?>
            <div class="p-gallery-album-card__thumb-empty">
                <i class="fas fa-images" aria-hidden="true"></i>
            </div>
        <?php endif; ?>
        <div class="p-gallery-album-card__info">
            <p class="p-gallery-album-card__title"><?= htmlspecialchars((string) $g->title) ?></p>
            <p class="p-gallery-album-card__count">
                <i class="fas fa-image" aria-hidden="true"></i> <?= $num_rows ?>
            </p>
        </div>
    </a>
    <?php } } ?>
</div>
<div class="u:mt-16">
    <?php
    if (isset($emptyPhotosets)) echo $emptyPhotosets;
    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    if (isset($requestStrNew)) $requestStr = $requestStrNew;
    $request = $requestStr . '/=page%d';
    require 'pages.php'; ?>
</div>
<?php } else { ?>
    <p class="p-profile-empty-hint"><?= $words->get('gallery.no.albums') ?></p>
<?php } ?>
