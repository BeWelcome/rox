<?php
    // get countries
    $query = "SELECT country,name FROM geonamescountries ORDER BY name";
    $countries = $this->model->bulkLookup($query);
