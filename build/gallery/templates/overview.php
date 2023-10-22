<?php

$words = new MOD_words();
$layoutbits = new MOD_layoutbits();
$thumbsize = $this->thumbsize;
if ($statement) {
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
    $p = PFunctions::paginate($statement, $page, $itemsPerPage);
    $statement = $p[0];

    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    $request = $requestStr.'/=page%d';
    require 'pages.php';
    $words = $this->words;
    ?>
    <div id="masonry-grid" class="row" data-masonry='{"percentPosition": true }'>
        <?php
        foreach ($statement as $d) {
        echo '<div class="col-sm-6 col-lg-4 mb-4">';
        echo '<div class="card">';
        echo '<a href="gallery/img?id='. $d->id .'" class="p-1" title="'. $d->title .'" data-toggle="lightbox" data-type="image" data-title="' . $d->title . '">';
        echo '<img class="mx-auto d-block img-fluid img-thumbnail" src="gallery/thumbimg?id='.$d->id.
            ($thumbsize ? '&t='.$thumbsize : '' ) . '" alt="' . $d->title . '">';
        echo '</a>';
        $title_short = ((strlen($d->title) >= 26) ? substr($d->title,0,20).'...' : $d->title);
        $loggedmember = isset($this->model) ? $this->model->getLoggedInMember : $this->loggedInMember;
        $edit = ($loggedmember && $loggedmember->Username == $d->user_handle);
        echo '<div class="card-body p-1">';
        if ($edit) {
            echo '<div class="o-checkbox u-m-4"><input type="checkbox" class="o-checkbox__input mr-0" name="imageId[]" value="' . $d->id . '"></div> ';
        }
        echo '<h6 class="card-title text-truncate"></h6><a href="gallery/img?id='. $d->id .'" alt="'. $d->title .'">'. $title_short . '</a>';
        if (null !== $d->albumId) {
            echo $words->getSilent('album') . '<a href="gallery/show/sets/' . $d->albumId . '" alt="' . $d->album . '">' . $d->album . '</a>';
        }

        if ($edit) {
            echo '<div class="card-text"><small class="text-muted">'.$layoutbits->ago(strtotime($d->created)).'</small>';
            echo '<a href="gallery/show/image/'.$d->id.'/edit" title="edit '. $d->title .'" class="btn btn-sm btn-outline-primary float-right"> <i class="fa fa-edit"></i></a>';
            echo '<a href="gallery/show/image/'. $d->id .'/delete" title="delete '. $d->title .'" class="btn btn-sm btn-danger float-right mr-1"><i class="fa fa-trash"></i></a></div>';
        }
        echo '</div>';
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
