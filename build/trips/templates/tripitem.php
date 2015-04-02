<h1><a href="/trips/<?= $tripId ?>"><?= $trip->name ?></a></h1>
<p><?= $trip->duration ?></p>
<?php
$words = $this->getWords();
$tripData = $trip->data;
if (!empty($trip->description )) { ?>
    <div class="row"><div class="col-xs-12"><?= $trip->description ?></div></div>
<?php }
if (!empty($tripData)) {
$count = count($tripData);
$counter = 0;
?>
<!-- Subtemplate: 2 columns 50/50 size -->
<div class="row">
    <!-- Contents for right subtemplate -->
    <?php
    foreach ($tripData as $subTripId => $subTrip) {
        $counter++;
        $highlight = false;
        if ($subTrip['location'] == $geoname) {
            $highlight = true;
        }
    if ($counter % 4 == 1) {
        echo '</div><div class="row">';
    }
        switch ($counter) {
            case $count:
                $color = 'red';
                $fa = 'fa-map-marker';
                break;
            case 1:
                $color = 'blue';
                $fa = 'fa-map-marker';
                break;
            default:
                $color = 'green';
                $fa = 'fa-caret-right';
                break;
        }
    ?>
    <div class="col-md-3">
        <i class="fa <?= $fa ?>" style="color: <?= $color ?>"></i>
        <?php if ($highlight) { ?>
            <span style="background-color: yellow" class="highlight">
        <?php } ?><strong><?= $subTrip['location'] ?></strong>, <?= $subTrip['startDate'] ?>
        <?php
            if ($subTrip['endDate'] <> '1970-01-01') { ?>
             - <?= $subTrip['endDate'] ?>
        <?php }
            if ($highlight) { ?>
                </span>
        <?php
        }

        ?><i class="fa fa-bed" style="color: <?= $color ?>"></i>
    </div>
<?php
        }
}
?>
</div>
