<?php
$userbarText = array();
$DModel = new DonateModel();
$TDonationArray = $DModel->getDonations();
$Stats = $DModel->getStatForDonations() ;

    $max=count($TDonationArray) ;
    $TotalDonations=$Stats->YearDonation ;
    $TotalDonationsNeeded = $Stats->YearNeededAmount ;
    if ($TotalDonations >= $TotalDonationsNeeded) {
        $Percent = 100;
    } else {
        $Percent = $TotalDonations *100/$TotalDonationsNeeded;
    }
    $BarState = -101 *$Percent/100;
    //$TextState = 202+$BarState;
    //if ($TextState > 160) $TextState = 160;
    $TextState= 50;
?>
<div class="bw_row">
<h3><?=$ww->Donate_DonationNeeded ?></h3>
<p><?=$ww->Donate_MoreInfo('<a href="donate">','</a>') ?></p>
<table>
    <tr>
        <td style="padding-left:0">
            <a href="donate" title="<?=$wwsilent->Donate_DonateNow ?>">
                <img src="images/misc/donationbar_small.png" alt="<?=$Percent?>%" class="percentImage" style="
                 background: white url(images/misc/donationbar_bg_small.png) top left no-repeat;
                 padding: 0;
                 margin: 5px 0 0 0;
                 background-position: 0pt <?=$BarState?>px;"
                />
            </a>
        </td>
        <td style="vertical-align: top">
            <div class="small">
                <p><?=$ww->Donate_bar_annual_2013($TotalDonations,$TotalDonationsNeeded) ?></p>
                <a href="donate" title="<?=$wwsilent->Donate_DonateNow ?>"><?=$wwsilent->Donate_DonateNow ?></a>
            </div>
            <?=$words->flushBuffer() ?>
        </td>
    </tr>
</table>
</div> <!-- row -->
