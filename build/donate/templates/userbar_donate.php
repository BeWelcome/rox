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
<div class="bw-row">
    <table>
        <tr>
            <td style="padding-left:0">
                <img src="images/misc/donationbar.png" alt="<?=$Percent?>%" 
                class="percentImage" style="
                     background: white url(images/misc/donationbar_bg.png) top left no-repeat;
                     padding: 0;
                     margin: 5px 0 0 0;
                     background-position: 0pt <?=$BarState?>px;"
                />
            </td>

            <td style="vertical-align: top">
                <div style="position: relative; top: <?=$TextState?>px">
                    <?=$words->getFormatted('Donate_bar_annual_2013',$TotalDonations,$TotalDonationsNeeded); ?>
                </div>
            </td>

        </tr>
    </table>
</div>
<?php } ?>
