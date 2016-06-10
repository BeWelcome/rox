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
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.

*/
// get current request
$request = PRequest::get()->request;

if (!isset($vars['errors']) || !is_array($vars['errors'])) {
    $vars['errors'] = array();
}

$words = new MOD_words($this->getSession());
$styles = array( 'highlight', 'blank' ); // alternating background for table rows
$iiMax = count($TMembers) ;
$purifier = MOD_htmlpure::getBasicHtmlPurifier();
?>
<p><?php echo $words->getFormatted("WeAreTotNumber",$TotMembers); ?></p>

<table class="full">
<?php if ($TMembers != false) { ?>
    <tr>
        <th><?php echo $words->getFormatted("Username");?></th>
        <th><?php echo $words->getFormatted("Location"); ?></th>
        <th><?php echo $words->getFormatted("ProfileSummary"); ?></th>
    </tr>
<?php
}
  for ($ii = 0; $ii < $iiMax; $ii++) {
    $m = $TMembers[$ii];
?>
    <tr class="<?php echo $styles[$ii%2]; ?>">
        <td align="center"><a class="username" href="members/<?php echo $m->Username; ?>"><?php echo $m->Username; ?></a>
						<?php if ($m->MemberStatus=='Pending' or $m->MemberStatus=='NeedMore') echo " [<i>",$m->MemberStatus,"</i>]" ; ?> 
						<br />
            <?php echo MOD_layoutbits::PIC_50_50($m->Username); ?>
        </td>
        <td><?php echo $m->countryname; ?></td>
        <td><?php echo $purifier->purify(stripslashes($words->mTrad($m->ProfileSummary,true))); ?></td>
        <td><?php
                // Deactivated on our servers. Only used for testing locally.
                /*
                if (IsAdmin()) {
                    echo $m->NbSec," sec ";
                }
                //  echo $m->ProfileSummary;
                if (IsAdmin()) {
                    echo $m->lastactivity;
                } 
                */
            ?>
        </td>
    </tr>
<?php
  }
?>
</table>

<?php
                // Deactivated on our servers. Only used for testing locally.
                /*
  if (IsAdmin()) {
     $iiMax = count($TGuests);
?>
    <table class="full">
        <tr><th colspan=2>Guest activity in last <?php echo $_SYSHCVOL['WhoIsOnlineDelayInMinutes']; ?> minutes </th></tr>
<?php
     for ($ii = 0; $ii < $iiMax; $ii++) {
          $m = $TGuests[$ii];
?>
        <tr>
            <td><?php echo $m->NbSec; ?> sec</td>
            <td><a href="/admin/adminlogs.php?ip=<?php echo $m->appearance; ?>"><?php echo $m->appearance; ?></a></td>
            <td><?php echo $m->lastactivity; ?></td>
        </tr>
<?php
      } // end of for ii
?>
    </table>
<?php
  }
                */

  if (!APP_User::login()) {
     echo "<p>",$words->getFormatted("OnlinePrivateProfilesAreNotDisplayed"), "</p>\n";
  }
?>
