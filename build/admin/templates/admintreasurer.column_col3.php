<?php
/*
Copyright (c) 2007-2009 BeVolunteer

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
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.
*/
    /** 
     * @author crumbking  
     */

    /** 
     * Tresasurer management overview template
     * 
     * @package Apps
     * @subpackage Admin
     */
$words = new MOD_words();
$R = MOD_right::get();
$hasRight = $R->hasRight('Treasurer');

if (isset($_SESSION['AdminTreasurerStatus'])) {
    $status = $_SESSION['AdminTreasurerStatus'];
    switch($status[0]) {
        case 'StartSuccess':
            $class = 'success';
            $word = $words->get('AdminTreasurerCampaignStarted');
            break;  
        case 'StartFailed':
            $class = 'error';
            $word = $words->get('AdminTreasurerCampaignStartFailed');
            break;  
        case 'StopSuccess':
            $class = 'success';
            $word = $words->get('AdminTreasurerCampaignStopped');
            break;  
        case 'StopFailed':
            $class = 'error';
            $word = $words->get('AdminTreasurerCampaignStopFailed');
            break;  
    }
    echo '<div class="' . $class . '">' . $word . '</div>';
    unset($_SESSION['AdminTreasurerStatus']);
}
if($this->campaign) {
    echo '<h3>' . $words->get("AdminTreasurerCurrentCampaign") . '</h3>';
} else {
    echo '<h3>' . $words->get("AdminTreasurerRecentDonations") . '</h3>';
} 
echo '<p>';
echo $words->get("AdminTreasurerCampaignStart", $this->campaignStartDate) . '<br />'
   . $words->get("AdminTreasurerCampaignNeededPerYear", $this->neededPerYear) . '<br />'
   . $words->get("AdminTreasurerDonationsAccrued", $this->donatedInCampaign);
echo '</p>';
?>

<table cellpadding="15" cellspacing="15" style="width: 100%;">
<tr class="highlight">
<th><?php echo $words->get('AdminTreasurerOverviewDate');?></th>
<th><?php echo $words->get('AdminTreasurerOverviewAmount');?></th>
<th><?php echo $words->get('AdminTreasurerOverviewComment');?></th>
<th><?php echo $words->get('AdminTreasurerOverviewCountry');?></th>
<th><?php echo $words->get('AdminTreasurerOverviewDetails');?></th>
<th><?php echo $words->get('AdminTreasurerOverviewEdit');?></th>
</tr>
<?php 
$ii = 0;
foreach($this->donations as $donation) {
    if ($ii % 2 == 1) {
        echo '<tr class="highlight">';
    } else {
        echo '<tr class="blank">';
    }
?>
<td><?php echo date("Y-m-d", strtotime($donation->created)); ?></td>
<td>€ <?php printf("%3.2f",$donation->Amount); ?></td>
<td><?php echo $donation->SystemComment; ?></td>
<td><?php echo $donation->CountryName; ?></td>
<td><?php echo MOD_member::getUsername($donation->IdMember) . " " . $donation->referencepaypal; ?></td>
<td><?php if (strpos($donation->SystemComment, 'Bank') !== false) {
    echo '<a href="admin/treasurer/edit/' . $donation->id . '">' . $words->get('AdminTreasurerOverviewEdit')
        . '</a>';
}        ?></td>
</tr>
<?php
    $ii++;
}
?>
</table>