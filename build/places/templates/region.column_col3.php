<?php
echo '<h2>' . $words->get('Cities') . '</h2>';
define('MINROWS',5); // minimum number of rows to be used before next column
define('MAXCOLS',4); // maximum number columns before extending rows beyond MINROWS
echo '<div id="places" class="clearfix">';
echo '<ul class="float_left">';

$listcnt = 0;
foreach ($this->cities as $city) {
    $listcnt++;
    if ($listcnt > max(MINROWS,ceil(count($this->cities)/MAXCOLS))) {
        echo '</ul>';
        echo '<ul class="float_left">';
        $listcnt = 1;
    }
    echo '<li><a class="highlighted" href="places/' . htmlspecialchars($this->countryName) . '/' . $this->countryCode
        . '/' . htmlspecialchars($this->regionName) . '/' . $this->regionCode . '/'
        . htmlspecialchars($city->city) . '/' . $city->geonameid . '">'. htmlentities($city->city, ENT_COMPAT, 'utf-8') .'</a> <span class="small grey">('.$city->NbMember.')</span>';
    echo '</li>';
}
echo '</ul></div>';
include 'memberlist.php';
?>