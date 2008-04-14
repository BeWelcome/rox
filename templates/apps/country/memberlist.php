<?php

$User = new APP_User;
$i18n = new MOD_i18n('apps/country/countryOverview.php');
$words = new MOD_words();
$text = $i18n->getText('text');

//------------------------------------------------------------------------------
// fage_value return a  the age value corresponding to date
function fage_value($dd) {
    $pieces = explode("-",$dd);
    if(count($pieces) != 3) return 0;
    list($year,$month,$day) = $pieces;
    $year_diff = date("Y") - $year;
    $month_diff = date("m") - $month;
    $day_diff = date("d") - $day;
    if ($month_diff < 0) $year_diff--;
    elseif (($month_diff==0) && ($day_diff < 0)) $year_diff--;
    return $year_diff;
} // end of fage_value

if (!$members) {
    return $text['no_members'];
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
    $p = PFunctions::paginate($members, $page, $itemsPerPage = 15);
    $members = $p[0];
?>
<ul class="floatbox">
<?php
    foreach ($members as $member) {
        $image = new MOD_images_Image('',$member->username);
        if ($member->HideBirthDate=="No") $member->age = floor(fage_value($member->BirthDate));
        else $member->age = $words->get("Hidden");
        echo '<li class="userpicbox float_left"><a href="user/'.$member->username.'">'.MOD_layoutbits::PIC_50_50($member->username,'',$style='float_left framed').'</a><p><a href="user/'.$member->username.'">'.$member->username.'</a><br />'.$words->getFormatted("yearsold",$member->age).'<br />'.$words->get("from").' '.$member->city.'</p></li>';
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