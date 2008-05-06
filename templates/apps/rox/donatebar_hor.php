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
        <table><tr><td style="padding-left:0">
        <img src="images/misc/donationbar_hor.png" alt="<?=$Percent?>%" class="percentImage" style="
         background: white url(images/misc/donationbar_hor_bg.png) top left no-repeat;
         padding: 0;
         margin: 5px 0 0 0;
         background-position: <?=$BarState?>px 0pt;"
        />
        </td>
        </tr>
        <tr>
        <td style="vertical-align: top">
        <div style="position: relative; top:-40px; font-weight: bold; font-size: 14px; color: white;"><?=$TotalDonations?> EUR</div>
        <p style="position: relative; top: -20px"><b><?=$words->getFormatted('Donate_bar_TitleHor'); ?></b></p>
        <p class="small" style="position: relative; top: -20px"><?=$words->getFormatted('Donate_bar',$TotalDonations,$TotalDonationsNeeded); ?></p>
        </td>
        </tr>
        </table>
        </div>

<?php } ?>