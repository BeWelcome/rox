<h1><a href="/trips/<?= $trip->id ?>"><?= $trip->title ?></a></h1>
<p><?= $trip->duration ?></p>
<?php
$words = $this->getWords(); ?>
<div class="bw_row"><div class="col-xs-12"><?= $trip->description ?></div></div>
<?php
$subtrips = $trip->getSubtrips();
$count = count($subtrips);
$counter = 0;
$divider = 3;
$class = "col-md-4";
?>
<!-- Subtemplate: 2 columns 50/50 size -->
<div class="bw_row">
    <!-- Contents for right subtemplate -->
    <?php
    foreach ($subtrips as $subtrip) {
        $details = $subtrip->getSubTripDetails();
        $counter++;
        $highlight = false;
        if ($details->geonameId == $geoname) {
            $highlight = true;
        }
        if ($counter % $divider == 1) {
            echo '</div><div class="bw_row">';
        }
        switch ($counter) {
            case 1:
            case $count:
                $color = 'blue';
                $fa = 'fa-map-marker';
                break;
            default:
                $color = 'green';
                $fa = 'fa-caret-right';
                break;
        }
    ?>
    <div class="<?= $class ?>">
        <i class="fa <?= $fa ?>" style="color: <?= $color ?>"></i>
        <?php if ($highlight) { ?>
            <span style="background-color: yellow" class="highlight">
        <?php } ?><strong><?= $details->shortName ?></strong>, <?= $details->arrival ?>
        <?php
            if ($details->departure <> $details->arrival) { ?>
             - <?= $details->departure ?>
        <?php }
            if ($highlight) { ?>
                </span>
        <?php
        }
        if (($details->options & TripsModel::TRIPS_OPTIONS_LOOKING_FOR_A_HOST) == TripsModel::TRIPS_OPTIONS_LOOKING_FOR_A_HOST) {
            echo '<i class="fa fa-bed"></i>';
        }
        if (($details->options & TripsModel::TRIPS_OPTIONS_LIKE_TO_MEETUP) == TripsModel::TRIPS_OPTIONS_LIKE_TO_MEETUP) {
            echo '<i class="fa fa-glass"></i>';
        }
        ?>
    </div>
<?php
}
?>
</div>
