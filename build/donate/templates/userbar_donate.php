<?php
$userbarText = array();
$words = new MOD_words();
?>
<?php
// display the donation bar if the parameters are set
if ($TDonationArray) {
    $max=count($TDonationArray) ;
    $TotalDonations=$Stat->YearDonation ;
    $TotalDonationsNeeded = $Stat->YearNeededAmount ;
    if ($TotalDonations >= $TotalDonationsNeeded) {
        $Percent = 100;
    } else {
        $Percent = $TotalDonations *100/$TotalDonationsNeeded;
    }
    $BarState = -202 *$Percent/100;
    //$TextState = 202+$BarState;
    //if ($TextState > 160) $TextState = 160;
    $TextState= 5;
?>
    <div class="row">
    <div class="col-12 mb-3">
        <div class="card p-3">
            <p class="h4 card-text text-center m-b-0"><?= $words->get('landing.beinvolved.goalfor'); ?> <?= $Stat->year ?>-<?= ($Stat->year +1 )?>: <strong>€<?= $TotalDonationsNeeded ?></strong></p>
            <progress class="progress progress-primary ma-0 w-100" value="<?= $TotalDonations ?>" max="<?= $TotalDonationsNeeded ?>"></progress>
            <p class="h4 card-text text-center"><strong>€<?= $TotalDonations ?></strong> received</p>
        </div>
    </div>
    </div>
<?php } ?>

