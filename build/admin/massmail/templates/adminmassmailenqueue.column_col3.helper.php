<?php
    // get countries
    if (count($this->enqueueCountries) <> 0) {
        $query = "SELECT country_id AS country, name FROM location WHERE feature_class = 'A' AND feature_code LIKE 'PCL%'"
            . " AND feature_code <> 'PCLH' AND country_id IN ('"
            . implode("', '", array_map(static fn ($code) => preg_replace('/[^A-Z]/', '', strtoupper(trim((string) $code))), $this->enqueueCountries)) . "') ORDER BY name";
    } else {
        $query = "SELECT country_id AS country, name FROM location WHERE feature_class = 'A' AND feature_code LIKE 'PCL%'"
            . " AND feature_code <> 'PCLH' ORDER BY name";
    }
    $countries = $this->model->BulkLookup($query);

    // get groups
    if (count($this->enqueueGroups) <> 0) {
        $query = "SELECT id, name AS Name FROM `group` WHERE id IN ('"
            . implode("', '", array_map('intval', $this->enqueueGroups)) . "') ORDER BY name";
    } else {
        $query = "SELECT id, name AS Name FROM `group` ORDER BY name";
    }
    $groups = $this->model->BulkLookup($query);
