<?php
$request = PRequest::get()->request;
$Gallery = new GalleryController;
$callbackId = $Gallery->updateGalleryProcess();
$vars = PPostHandler::getVars($callbackId);
$words = $this->getWords();

$layoutbits = new MOD_layoutbits();
$thumbsize = $this->thumbsize;

echo $words->flushBuffer();

if ($statement) {
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
    
    foreach ($statement as $d) {
        $title_short = ((strlen($d->title) >= 26) ? substr($d->title,0,25).'...' : $d->title);
        $d->HTML = '
        <div class="img thumb" style="width: 244px;">
            <a href="gallery/show/image/'.$d->id.'" id="image_link_'.$d->id.'"><img class="framed" src="gallery/thumbimg?id='.$d->id.($thumbsize ? '&t='.$thumbsize : '').'" alt="image" style="margin: 5px 0; float:none;" /></a>';

        $d->HTML .= '<h4 class="clearfix">';
        if ($this->loggedInMember && $this->loggedInMember->Username == $d->user_handle) {
            $d->HTML .= '<input type="checkbox" class="thumb_check input_check" name="imageId[]" onchange="highlightMe($(\'image_link_'.$d->id.'\'),this.checked);" value="'.$d->id.'">&nbsp;&nbsp; ';
        }
        $d->HTML .= '<a href="gallery/show/image/'.$d->id.'" title="'.$d->title.'">'.$title_short.'</a><a href="gallery/img?id='.$d->id.'" class=\'lightview\' rel=\'gallery[BestOf]\'><img src="styles/css/minimal/images/icon_image_expand.gif" style="float:right" title="'.$words->getSilent('Preview image').'"></a>'.$words->flushBuffer().'</h4>';
        
        $d->HTML .= '
            <p class="small">
                '.$layoutbits->ago(strtotime($d->created)).' '.$words->getFormatted('by').'
                <a href="members/'.$d->user_handle.'">'.$d->user_handle.'</a>. 
                <a href="gallery/show/user/'.$d->user_handle.'" title="'.$words->getSilent('galleryUserOthers',$d->user_handle).'">
                <img src="styles/css/minimal/images/iconsfam/pictures.png" style="float: none">
                </a>'.$words->flushBuffer().'
            </p>
        </div>';
    }
    $array_split = array();
    $k = 0;
    for ($x=0; $x < count($statement); $x++){
        if (!($x % 3)){ $k++; }
        $array_split[$k][] = $statement[$x];
    }
    foreach ($array_split as $dd) {
?>
        <!-- Subtemplate: 3 Spalten mit 33/33/33 Teilung -->
        <div class="subcolumns fixed_columns_list">
          <div class="c33l">
            <div class="subcl">
              <!-- Inhalt linker Block -->
              <? if (isset($dd[0])) echo $dd[0]->HTML; ?>
            </div>
          </div>

          <div class="c33l">
            <div class="subc">
              <!-- Inhalt linker Block -->
              <? if (isset($dd[1])) echo $dd[1]->HTML; ?>
            </div>
          </div>

          <div class="c33r">
            <div class="subcr">
              <!-- Inhalt rechter Block -->
              <? if (isset($dd[2])) echo $dd[2]->HTML; ?>
            </div>
          </div>
        </div>
<?php
    }
    echo '<div class="clearfix">';
    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    $request = $requestStr.'/=page%d';
    require 'pages.php';
    echo '</div>';
}
?>
