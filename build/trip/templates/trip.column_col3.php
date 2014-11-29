<?php

$trips = $this->trips;
foreach($trips as $trip) {
	require 'tripitem.php';
}

$this->pager->render();
