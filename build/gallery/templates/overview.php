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

    echo '<div class="row">';
    foreach ($statement as $d) {
    	echo '<div class="col-12 col-sm-6 col-lg-4">';
    $title_short = ((strlen($d->title) >= 26) ? substr($d->title,0,20).'...' : $d->title);
    echo '<a href="gallery/show/image/'.$d->id.'" id="image_link_'.$d->id.'"><img class="framed w-100" src="gallery/thumbimg?id='.$d->id.($thumbsize ? '&t='.$thumbsize : '').'" alt="image"></a>';

    echo '<div class="w-100 bg-white h6 px-2">';
    $loggedmember = isset($this->model) ? $this->model->getLoggedInMember : $this->loggedInMember;
    if ($loggedmember && $loggedmember->Username == $d->user_handle) {
        echo '<input type="checkbox" class="input_check mr-2" name="imageId[]" onchange="highlightMe($(\'image_link_'.$d->id.'\'),this.checked);" value="'.$d->id.'">';
        echo '<a href="gallery/show/image/'. $d->id .'" title="'. $d->title .'">'. $title_short . '</a>';
        echo '<a href="gallery/img?id='. $d->id .'" class="lightview" rel="gallery[BestOf]"><i class="fa fa-expand float-right" title="'. $words->getSilent('Preview image') .'"></i></a>'. $words->flushBuffer() . '</a></div>';
        echo '<p class="small">'.$layoutbits->ago(strtotime($d->created)).' '.$words->getFormatted('by') .' <a href="members/'.$d->user_handle.'">'.$d->user_handle.'</a>';
        echo '<a href="gallery/show/user/'.$d->user_handle.'" title="'.$words->getSilent('galleryUserOthers',$d->user_handle).'"><i class="fa fa-image ml-1"></i></a>'.$words->flushBuffer().'</p>';
    }
        echo '</div>';
    }
    echo '</div>';

    echo '<div class="row w-100">';
    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    $request = $requestStr.'/=page%d';
    if (!isset($nopagination) || !$nopagination)
    require 'pages.php';
    echo '</div>';
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