<h1><a href="/trips/<?= $trip->id ?>"><?= $trip->name ?></a></h1>
<p><?= $trip->duration ?></p>
<?php
$words = $this->getWords(); ?>
<div class="row"><div class="col-xs-12"><?= $trip->description ?></div></div>
<?php
$count = count($trip->subtrips);
$counter = 0;
?>
<!-- Subtemplate: 2 columns 50/50 size -->
<div class="row">
    <!-- Contents for right subtemplate -->
    <?php
    foreach ($trip->subtrips as $subTrip) {
        $counter++;
        $highlight = false;
        if ($subTrip->geonameId == $geoname) {
            $highlight = true;
        }
    if ($counter % 4 == 1) {
        echo '</div><div class="row">';
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
        if ($subTrip->additional & 1 == 1) {
            echo '<i class="fa fa-bed"></i>';
        }
        if ($subTrip->additional & 2 == 2) {
            echo '<i class="fa fa-drink"></i>';
        }
        ?>
    </div>
<?php
}
?>
</div>
