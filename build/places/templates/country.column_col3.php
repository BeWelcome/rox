<h2><?php echo $words->get('region_overview_title'); ?></h2>
<?php
define('MINROWS',1); // minimum number of rows to be used before next column
define('MAXCOLS',5); // maximum number columns before extending rows beyound MINROWS
echo '<div class="floatbox places">';
echo '<ul class="float_left">';
$listcnt = 0;
$memberCount = 0;
foreach ($this->regions as $code => $region) {
    // counting total members for possible login-to-see-more message
    $memberCount += $region['number'];

    $listcnt++;
    if ($listcnt > max(MINROWS,ceil(count($this->regions)/MAXCOLS))) {
        echo '</ul>';
        echo '<ul class="float_left">';
        $listcnt = 1;
    }
    echo '<li><a ';
    if ($region['number'] != 0) {
        echo 'class="highlighted" ';
    }
    echo 'href="places/' . htmlspecialchars($this->countryName) . '/' . $this->countryCode . '/'
            . htmlspecialchars($region['name']) . '/' . $code . '">'. htmlspecialchars($region['name']) . '</a>';
    if ($region['number'] != 0) {
        echo ' <span class="small grey">('.$region['number'].')</span>';
    }
    echo '</li>';
}
echo '</ul></div>';
include_once 'memberlist.php';
include 'placeinfo.php';
?>
