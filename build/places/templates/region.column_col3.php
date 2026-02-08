<div class="row">
<?php
echo '<div class="col-12"><h2>' . $words->get('Cities') . '</h2></div>';

echo '<div class="col-12 col-md-6 col-lg-3">';
$listcnt = 0;
$number_of_cities = count($this->cities);
$per_column = round($number_of_cities/4);
$per_column = $per_column == 0 ? 1 : $per_column;

foreach ($this->cities as $city) {
    $listcnt++;

    if ($listcnt > $per_column) {
        echo '</div><div class="col-12 col-md-6 col-lg-3">';
        $listcnt = 1;
    }

    echo '<div class="u:p-1"><a href="places/' . htmlspecialchars((string) $this->countryName) . '/' . $this->countryCode
         . '/' . htmlspecialchars((string) $this->regionName) . '/' . $this->regionCode . '/'
         . htmlspecialchars((string) $city->city) . '/' . $city->geoname_id . '">'. htmlentities((string) $city->city, ENT_COMPAT, 'utf-8') .'</a><span class="small ml-1 badge badge-primary">' . $city->count . '</span>';
    echo '</div>';

}
echo '</div></div>';

include 'memberlist.php';
?>
</div>
