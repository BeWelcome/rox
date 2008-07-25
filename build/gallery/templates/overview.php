<?php

$words = new MOD_words();
$User = new APP_User;
?>
<script type="text/javascript">
function highlightMe(element,check) {
    if (check == true) {
        new Effect.Highlight(element, { startcolor: '#ffffff', endcolor: '#ffff99', restorecolor: '#ffff99' });
        return true;
    } else {
        new Effect.Highlight(element, { startcolor: '#ffff99', endcolor: '#ffffff', restorecolor: '#ffffff' });
        return true;
    }
}
function checkall(formname,checkname,thestate){
    var el_collection=eval("document.forms."+formname+"."+checkname)
    for (c=0;c<el_collection.length;c++) {
    el_collection[c].checked=thestate
    }
}
function selectAll(obj) { 
var checkBoxes = document.getElementsByClassName('thumb_check'); 
var checker = document.getElementsByClassName('checker');
for (i = 0; i < checker.length; i++) { 
checker[i].checked = obj.checked;
}
for (i = 0; i < checkBoxes.length; i++) { 
if (obj.checked == true) {
checkBoxes[i].checked = true; // this checks all the boxes 
highlightMe(checkBoxes[i].parentNode.parentNode, true);
} else { 
checkBoxes[i].checked = false; // this unchecks all the boxes 
highlightMe(checkBoxes[i].parentNode.parentNode, false);
} 
} 
}

// build regular expression object to find empty string or any number of spaces
var blankRE=/^\s*$/;
function CheckEmpty(TextObject)
{
if(blankRE.test(TextObject.value))
{
return false;} else return true;
}
</script>
<?php
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
<div class="img thumb float_left" style="width: 160px; margin: 10px 10px 30px 10px; padding: 10px">
    <a href="gallery/show/image/'.$d->id.'"><img class="framed" src="gallery/thumbimg?id='.$d->id.'" alt="image" style="margin: 5px 0; float:none;" /></a>';

    echo '<h4>';
if ($User && $User->getHandle() == $d->user_handle) {
    echo '<input type="checkbox" class="thumb_check" name="imageId[]" onchange="highlightMe(this.parentNode.parentNode,this.checked);" value="'.$d->id.'">&nbsp;&nbsp; ';
}
?>
    <a href="gallery/show/image/<?=$d->id ?>" title="<?=$d->title ?>"><?php if (strlen($d->title) >= 20) echo substr($d->title,0,15).'...'; else echo $d->title; ?></a></h4>
<?php echo '
    <p class="small">
        '.$d->width.'x'.$d->height.'; '.$d->mimetype.'; '.$words->getFormatted('GalleryUploadedBy').':
        <a href="bw/member.php?cid='.$d->user_handle.'">'.$d->user_handle.'</a>. 
        <a href="gallery/img?id='.$d->id.'" class=\'lightview\' rel=\'gallery[BestOf]\'>
        <img src="styles/YAML/images/iconsfam/pictures.png" style="float: none">
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