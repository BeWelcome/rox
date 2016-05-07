<?php
if (!isset($geoname)) {
    $geoname = "";
}

if ($this->trips) {
    $layoutbits = new MOD_layoutbits();

    foreach ($this->trips as $tripId) {
        $trip = new Trip($tripId);
        require 'tripitem.php';
    }

    $this->pager->render();
} else {
    echo $this->getWords()->getFormatted('TripsNoTripsFound');
}
?>