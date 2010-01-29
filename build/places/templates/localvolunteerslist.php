<?php

$User = new APP_User;
$words = new MOD_words();

// This was quicly duplicated from the memberslist.php template, this imply om redudancies

//echo "count \$volunteers=",count($volunteers) ;
if (empty($volunteers)) {
    echo $words->getFormatted('no_localvolunteers_yet','<a href="about/feedback">','</a>');
} else {
    $request = PRequest::get()->request;
    $requestStr = implode('/', $request);
    $matches = array();
    if (preg_match('%/=page(\d+)%', $requestStr, $matches)) {
        $page = $matches[1];
        $requestStr = preg_replace('%/=page(\d+)%', '', $requestStr);
    } else {
        $page = 1;
    }
    $p = PFunctions::paginate($volunteers, $page, $itemsPerPage = 15);
    $volunteers = $p[0];
?>
<ul class="floatbox">
<?php
    foreach ($volunteers as $member) {
        $image = new MOD_images_Image('',$member->username);
        echo '<li class="userpicbox_vol float_left">';
        echo MOD_layoutbits::PIC_50_50($member->username,'',$style='framed float_left');
        echo '<p><a href="member/'.$member->username.'">'.$member->username.'</a>' ;
        echo '<br /><span class="small">'.$member->city.'</span>' ;
        echo '<br />',$words->mTrad($member->VolComment,true) ;
        echo '</p>';
        echo '</li>';
    }
    ?>
    </ul>
<?php
    $pages = $p[1];
    $maxPage = $p[2];
    $currentPage = $page;
    $request = $requestStr.'/=page%d';
    require TEMPLATE_DIR.'misc/pages.php';
}

?>
