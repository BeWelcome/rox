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
    if ($counter % 4 == 1) {
        echo '</div><div class="row">';
    }
        switch ($counter) {
            case 1: $flag = 'flag_yellow.png';
                break;
            case $count:
                $flag = 'flag_red.png';
                break;
            default:
                $flag = 'bullet_go.png';
                break;
        }
    ?>
    <div class="col-md-3">
        <img src="styles/css/minimal/images/iconsfam/<?= $flag ?>" alt="flag" />
        <strong><?= $subTrip['location'] ?></strong>, <?= $subTrip['startdate'] ?>
        <?php
            if ($subTrip['enddate'] <> '1970-01-01') { ?>
             - <?= $subTrip['enddate'] ?>
        <?php }
        ?>
    </div>
<?php
        }
}
?>
</div>
