<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple PlaceSuite 330, 
Boston, MA  02111-1307, USA.

*/
$words = new MOD_words();
$DModel = new DonateModel();
$TDonationArray = $DModel->getDonations();
$Stats = $DModel->getStatForDonations() ;

// display the horizontal donation bar if the parameters are set
if ($TDonationArray) {
    $max=count($TDonationArray) ;
    $TotalDonations=$Stats->QuaterDonation ;
    $TotalDonationsNeeded = $Stats->QuaterNeededAmount ;
    $Percent = $TotalDonations *100/$TotalDonationsNeeded;
    
    //$TextState = 202+$BarState;
    //if ($TextState > 160) $TextState = 160;
    $TextState= 50;
    $BarState = 202 *$Percent/100 - 202;
    
?>

    <div class="row">
        <h3 style="border-bottom: none; color: #fff"><b><?=$words->getFormatted('Donate_bar_TitleHor'); ?></b></h3>
        <div style="position: relative; top:30px; left: 10px; font-weight: bold; font-size: 14px; color: white;">
            <a href="donate" alt="<?=$words->getFormatted('Donate_DonateNow'); ?>"><?=$TotalDonations?> EUR</a>
        </div>
        <a href="donate" alt="<?=$words->getFormatted('Donate_DonateNow'); ?>">
        <img src="images/misc/donationbar_orange_hor.png" alt="<?=$Percent?>%" class="percentImage float_left" style="
         background: white url(images/misc/donationbar_hor_bg.png) top left no-repeat;
         padding: 0;
         margin: 0 15px 0 0;
         background-position: <?=$BarState?>px 0pt;"
        />
        </a>
        <p class="small"><?=$words->getFormatted('Donate_bar',$TotalDonations,$TotalDonationsNeeded); ?> <a href="donate" alt="<?=$words->getFormatted('Donate_DonateNow'); ?>"><?=$words->getFormatted('DonateNow'); ?></a></p>
        <p></p>
    </div>

<?php } ?>