<?php

$words = new MOD_words($this->getSession());
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

    echo '<div class="clearfix thumb_wrapper">';
    foreach ($statement as $d) {
    	echo '
<div class="img thumb float_left size'.$thumbsize.'">
    <a href="gallery/show/image/'.$d->id.'" id="image_link_'.$d->id.'"><img class="framed" src="gallery/thumbimg?id='.$d->id.($thumbsize ? '&t='.$thumbsize : '').'" alt="image" style="margin: 5px 0; float:none;" /></a>';

    echo '<h4>';
    $loggedmember = isset($this->model) ? $this->model->getLoggedInMember : $this->loggedInMember;
    if ($loggedmember && $loggedmember->Username == $d->user_handle) {
        echo '<input type="checkbox" class="input_check" name="imageId[]" onchange="highlightMe($(\'image_link_'.$d->id.'\'),this.checked);" value="'.$d->id.'">&nbsp;&nbsp; ';
}
?>
    <a href="gallery/show/image/<?=$d->id ?>" title="<?=$d->title ?>"><?php if (strlen($d->title) >= 20) echo substr($d->title,0,15).'...'; else echo $d->title; ?></a> <a href="gallery/img?id=<?=$d->id?>" class="lightview" rel="gallery[BestOf]"><img src="styles/css/minimal/images/icon_image_expand.gif" title="<?=$words->getSilent('Preview image')?>" ><?php echo $words->flushBuffer(); ?></a></h4>
<?php 
echo '
    <p class="small">
        '.$layoutbits->ago(strtotime($d->created)).' '.$words->getFormatted('by').'
        <a href="members/'.$d->user_handle.'">'.$d->user_handle.'</a>. 
        <a href="gallery/show/user/'.$d->user_handle.'" title="'.$words->getSilent('galleryUserOthers',$d->user_handle).'">
        <img src="styles/css/minimal/images/iconsfam/pictures.png" style="float: none">
        </a>'.$words->flushBuffer().'
    </p>
    </div>';
    }
    echo '</div>';
    echo '<div class="clearfix">';
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
</script>
