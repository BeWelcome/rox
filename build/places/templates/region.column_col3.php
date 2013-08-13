<?php
define('MINROWS',5); // minimum number of rows to be used before next column
define('MAXCOLS',3); // maximum number columns before extending rows beyound MINROWS
echo '<div class="floatbox places">';
echo '<ul class="float_left">';

$listcnt = 0;
$memberCount = 0;
foreach ($this->cities as $city) {
    $memberCount += $city->NbMember;
    $listcnt++;
    if ($listcnt > max(MINROWS,ceil(count($this->cities)/MAXCOLS))) {
        echo '</ul>';
        echo '<ul class="float_left">';
        $listcnt = 1;
    }
    echo '<li><a class="highlighted" href="places/' . htmlspecialchars($this->countryName) . '/' . $this->countryCode
        . '/' . htmlspecialchars($this->regionName) . '/' . $this->regionCode . '/'
        . htmlspecialchars($city->city) . '/' . $city->geonameid . '">'. htmlentities($city->city, ENT_COMPAT, 'utf-8') .' <span class="small grey">('.$city->NbMember.')</span>';
    echo '</a></li>';
}
echo '</ul></div>';
include_once 'memberlist.php';
?>