<?php
$userbarText = array();
$words = new MOD_words();
$DModel = new DonateModel();
$TDonationArray = $DModel->getDonations();
$Stats = $DModel->getStatForDonations() ;
?>
<?php
// display the donation bar if the parameters are set
if ($TDonationArray) {
    $max=count($TDonationArray) ;
    $TotalDonations=$Stats->QuaterDonation ;
    $TotalDonationsNeeded = $Stats->QuaterNeededAmount ;
    $Percent = $TotalDonations *100/$TotalDonationsNeeded;
    
    $BarState = -101 *$Percent/100;
    //$TextState = 202+$BarState;
    //if ($TextState > 160) $TextState = 160;
    $TextState= 50;
?>
    <div class="row">
        <h3><?=$words->getFormatted('Donate_DonationNeeded'); ?></h3>
        <p><?=$words->getFormatted('Donate_MoreInfo','<a href="donate">','</a>'); ?></p>
        <table>
            <tr>
            <td style="padding-left:0">
                <a href="donate" alt="<?=$words->getBuffered('Donate_DonateNow'); ?>">
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
                <br />
                <?=$words->getFormatted('Donate_bar',$TotalDonations,$TotalDonationsNeeded); ?>
                <br />
                <a href="donate" alt="<?=$words->getBuffered('Donate_DonateNow'); ?>">
                    <?=$words->getBuffered('Donate_DonateNow')?>
                </a>
                </div>
                <?=$words->flushBuffer() ?>
            </td>
            </tr>
        </table>
    </div>
<?php } ?>
           
		   