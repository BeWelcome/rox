<?php

$words = new MOD_words();
$layoutbits = new MOD_layoutbits();
$thumbsize = $this->thumbsize;
if ($statement) {
    $request = PRequest::get()->request;
    $requestStr = implode('/', $request);
    $matches = [];
    if (preg_match('%/=page(\d+)%', $requestStr, $matches)) {
        $page = $matches[1];
        $requestStr = preg_replace('%/=page(\d+)%', '', $requestStr);
    } else {
        $page = 1;
    }
    if (!isset($itemsPerPage)) $itemsPerPage = 12;
    $p = PFunctions::paginate($statement, $page, $itemsPerPage);
    $statement = $p[0];

    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    $request = $requestStr.'/=page%d';
    $words = $this->words;
    ?>
    <div id="masonry-grid" class="row">
        <?php
        foreach ($statement as $d) {
        $title_short = ((strlen((string) $d->title) >= 26) ? substr((string) $d->title,0,20).'...' : $d->title);
        $loggedmember = isset($this->model) ? $this->model->getLoggedInMember : $this->loggedInMember;
        $edit = ($loggedmember && $loggedmember->Username == $d->user_handle);
        $photoTitle = htmlspecialchars((string) $d->title, ENT_QUOTES, 'UTF-8');
        $photoDesc  = htmlspecialchars((string) ($d->description ?? ''), ENT_QUOTES, 'UTF-8');
        $dataAttrs  = $edit
            ? ' data-photo-id="'.(int)$d->id.'"'
              .' data-photo-title="'.$photoTitle.'"'
              .' data-photo-desc="'.$photoDesc.'"'
              .' data-photo-thumb="gallery/thumbimg?id='.(int)$d->id.'&amp;t=2"'
              .' data-photo-delete="gallery/show/image/'.(int)$d->id.'/delete"'
            : '';
        echo '<div class="col-sm-6 col-lg-4"'.$dataAttrs.'>';
        echo '<div class="card">';
        if ($edit) {
            echo '<label class="p-gallery-manage__image-select" aria-label="Select image ' . $photoTitle . '">';
            echo '<input type="checkbox" class="p-gallery-manage__image-select-input" name="imageId[]" value="' . $d->id . '">';
            echo '<span class="p-gallery-manage__image-select-box" aria-hidden="true"></span>';
            echo '</label>';
            echo '<a href="gallery/img?id='. $d->id .'" class="p-gallery-manage__photo-trigger" title="'. $d->title .'">';
        } else {
            echo '<a href="gallery/img?id='. $d->id .'" class="p-1" title="'. $d->title .'" data-toggle="lightbox" data-type="image" data-title="' . $d->title . '">';
        }
        echo '<img class="img-fluid" src="gallery/thumbimg?id='.$d->id.
            ($thumbsize ? '&t='.$thumbsize : '' ) . '" alt="' . $d->title . '">';
        echo '</a>';
        if (!$edit) {
            echo '<div class="card-body p-1">';
            echo '<h6 class="card-title text-truncate"></h6><a href="gallery/img?id='. $d->id .'" alt="'. $d->title .'">'. $title_short . '</a>';
            if (null !== $d->albumId) {
                echo $words->getSilent('album') . '<a href="gallery/show/sets/' . $d->albumId . '" alt="' . $d->album . '">' . $d->album . '</a>';
            }
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';

    require 'pages.php';
}
?>
<script type="text/javascript">
    function toggle(source) {
        checkboxes = document.getElementsByName('imageId[]');
        for(var i=0, n=checkboxes.length;i<n;i++) {
            checkboxes[i].checked = source.checked;
        }
    }
</script>
