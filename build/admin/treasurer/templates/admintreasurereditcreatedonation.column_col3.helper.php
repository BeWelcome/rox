<?php
    // get countries
    $query = "SELECT iso_alpha2,name FROM geonames_countries ORDER BY name";
    $countries = $this->model->BulkLookup($query);
?>