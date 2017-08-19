<?php

$columns = array();
$lastcontinent = "";
foreach($this->continents as $continent => $value) {

    echo '<div class="col-6 col-md-4 col-lg-2">';
    echo '<h3>' . $value[0] . '</h3>';

    foreach ($this->countries[$continent] as $country) {

        echo '<p><i class="famfamfam-flag-' . strtolower($country->country) . '"></i><span class="pr-1"></span>';
        if ($country->number) {
            echo '<a href="/places/' . htmlspecialchars($country->name) . '/' . $country->country . '">';
        }
        echo htmlspecialchars($country->name);;
        if ($country->number) {
            echo '</a><span class="ml-1 badge badge-secondary">' . $country->number . '</span>';
        }
        echo '</p>';

    }

   echo '</div>';
}
?>