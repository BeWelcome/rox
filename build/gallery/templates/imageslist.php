<?php

$words = new MOD_words();
$User = new APP_User;
$layoutbits = new MOD_layoutbits();

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
    if (!isset($itemsPerPage)) $itemsPerPage = 10;
    $p = PFunctions::paginate($statement, $page, $itemsPerPage);
    $statement = $p[0];

    echo '<div class="floatbox">';
    foreach ($statement as $d) {
    	echo '
<div class="img thumb float_left" style="width: 160px; height: 180px; margin: 0; padding: 10px">
<a href="gallery/show/image/'.$d->id.'"><img id="image_link_'.$d->id.'" class="framed" src="gallery/thumbimg?id='.$d->id.'" alt="image" style="margin: 5px 0; float:none;" /></a>';

    echo '<h4>';
if ($User && $User->getHandle() == $d->user_handle) {
    echo '<input type="checkbox" class="thumb_check" name="imageId[]" onchange="highlightMe($(\'image_link_'.$d->id.'\'),this.checked);" value="'.$d->id.'">&nbsp;&nbsp; ';
}
?>
    <a href="gallery/show/image/<?=$d->id ?>" title="<?=$d->title ?>"><?php if (strlen($d->title) >= 20) echo substr($d->title,0,15).'...'; else echo $d->title; ?></a></h4>
<?php 
echo '
    <p class="small">
        '.$layoutbits->ago(strtotime($d->created)).' '.$words->getFormatted('by').'
        <a href="bw/member.php?cid='.$d->user_handle.'">'.$d->user_handle.'</a>. 
        <a href="gallery/img?id='.$d->id.'" class=\'lightview\' rel=\'gallery[BestOf]\'>
        <img src="styles/css/minimal/images/iconsfam/pictures.png" style="float: none">
        </a>
    </p>
    </div>';
    }
    echo '</div>';
    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    $request = $requestStr.'/=page%d';
    require TEMPLATE_DIR.'misc/pages.php';
}
?>
<script type='text/javascript'>
// late_loader.queueObjectMethod('common', 'highlightMe');
// late_loader.queueObjectMethod('common', 'checkAll');
// late_loader.queueObjectMethod('common', 'selectAll');
// late_loader.queueObjectMethod('common', 'checkEmpty');
</script>