<?php
echo '<div class="col-12 p-0"><h2>' . $words->get('Cities') . '</h2></div>';

echo '<div class="col-12 col-md-6 col-lg-3 p-0">';
$listcnt = 0;
$number_of_cities = count($this->cities);
$per_column = round($number_of_cities/4);

foreach ($this->cities as $city) {
    $listcnt++;

    if ($listcnt > $per_column) {
        echo '</div><div class="col-12 col-md-6 col-lg-3 p-0">';
        $listcnt = 1;
    }

    echo '<div class="p-1"><a href="places/' . htmlspecialchars($this->countryName) . '/' . $this->countryCode
         . '/' . htmlspecialchars($this->regionName) . '/' . $this->regionCode . '/'
         . htmlspecialchars($city->city) . '/' . $city->geonameid . '">'. htmlentities($city->city, ENT_COMPAT, 'utf-8') .'</a><span class="small ml-1 badge badge-primary">' . $city->NbMember . '</span>';
    echo '</div>';

}
echo '</div>';
include 'memberlist.php';
?>