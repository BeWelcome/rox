<?php
    // get countries
    if (count($this->enqueueCountries) <> 0) {
        $query = "SELECT iso_alpha2,name FROM geonames_countries WHERE iso_alpha2 IN ('"
            . implode("', '", $this->enqueueCountries) . "') ORDER BY name";
    } else {
        $query = "SELECT iso_alpha2,name FROM geonames_countries ORDER BY name";
    }
    $countries = $this->model->BulkLookup($query);

    // get groups
    if (count($this->enqueueGroups) <> 0) {
        $query = "SELECT id,Name FROM groups WHERE id IN ('"
            . implode("', '", $this->enqueueGroups) . "') ORDER BY Name";
    } else {
        $query = "SELECT id, Name FROM groups ORDER BY Name";
    }
    $groups = $this->model->BulkLookup($query);
?>