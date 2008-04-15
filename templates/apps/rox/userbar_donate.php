<?php
$userbarText = array();
$words = new MOD_words();
?>
<?php
// display the donation bar if the parameters are set
if ($TDonationArray) {
    $max=count($TDonationArray) ;
    for ($ii=0;$ii<$max;$ii++) {
    $info_styles = array(0 => "class=\"blank\"", 1 => "class=\"highlight\"");
        static $iii = 0;
        $T=$TDonationArray[$ii] ;
        $string = $info_styles[($iii++%2)]; // this displays the <tr>
    }
	  	 $TotalDonations=$Stat->QuaterDonation ;
        $TotalDonationsNeeded = $Stat->QuaterNeededAmount ;
        $Percent = $TotalDonations *100/$TotalDonationsNeeded;
        
        $BarState = -202 *$Percent/100;
        //$TextState = 202+$BarState;
        //if ($TextState > 160) $TextState = 160;
        $TextState= 50;
?>
        <div class="row">
                    <table><tr><td style="padding-left:0">
                    <img src="images/misc/donationbar.png" alt="<?=$Percent?>%" class="percentImage" style="
                     background: white url(images/misc/donationbar_bg.png) top left no-repeat;
                     padding: 0;
                     margin: 5px 0 0 0;
                     background-position: 0pt <?=$BarState?>px;"
                    />
                    </td>
                    <td style="vertical-align: top">
                    <div style="position: relative; top: <?=$TextState?>px">
                    <?=$words->getFormatted('Donate_bar',$TotalDonations,$TotalDonationsNeeded); ?>
                    </div>
                    </td>
                    </tr>
                    </table>
                    </div>
<?php } ?>
           <h3><?php echo $words->get('Donate_Stats');?></h3>
           <p><?php echo $words->getFormatted('Donate_StatsText',$Stat->MonthNeededAmount,$Stat->YearDonation,$Stat->QuaterDonation); ?></p>
		   