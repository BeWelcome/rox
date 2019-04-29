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

    echo '<div class="row">';
    foreach ($statement as $d) {
    	echo '<div class="col-12 col-md-6 mb-2">';
    	echo '<div class="card">';
        echo '<a href="test"><img src="gallery/thumbimg?id='.$d->id.
            ($thumbsize ? '&t='.$thumbsize : '' ) . '" class="card-img-top" alt="{$d->title}"></a>';
    $title_short = ((strlen($d->title) >= 26) ? substr($d->title,0,20).'...' : $d->title);
        echo '<div class="card-body">';
        echo '<h6 class="card-title">';
        $loggedmember = isset($this->model) ? $this->model->getLoggedInMember : $this->loggedInMember;
        $edit = ($loggedmember && $loggedmember->Username == $d->user_handle);
        if ($edit) {
            echo '<input type="checkbox" class="form-check-inline" name="imageId[]" value="' . $d->id . '">&nbsp;';
        }
        echo '<a href="gallery/img?id='. $d->id .'" title="'. $d->title .'" data-toggle="lightbox" data-type="image">'. $title_short . '</a></h6>';

        if ($edit) {
            echo '<div class="d-inline"><small class="text-muted">'.$layoutbits->ago(strtotime($d->created)).'</small>';
            echo '<a href="gallery/show/image/'.$d->id.'/edit" title="edit '. $d->title .'" class="btn btn-sm btn-outline-primary float-right ml-1"> <i class="fa fa-edit"></i></a>';
            echo '<a href="gallery/show/image/'. $d->id .'/delete" title="delete '. $d->title .'" class="btn btn-sm btn-danger float-right"><i class="fa fa-trash"></i></a></div>';
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
// late_loader.queueObjectMethod('common', 'highlightMe');
// late_loader.queueObjectMethod('common', 'checkAll');
// late_loader.queueObjectMethod('common', 'selectAll');
// late_loader.queueObjectMethod('common', 'checkEmpty');

function toggle(source) {
    checkboxes = document.getElementsByName('imageId[]');
    for(var i=0, n=checkboxes.length;i<n;i++) {
        checkboxes[i].checked = source.checked;
    }
}
</script>