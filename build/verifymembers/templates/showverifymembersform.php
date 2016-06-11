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

$words = new MOD_words();
$styles = array( 'highlight', 'blank' ); // alternating background for table rows
$iiMax = count($TMembers)
?>
<p><?=$words->getFormatted("WeAreTotNumber",$TotMembers) ?></p>

<table class="full">

<?php if ($TMembers != false) { ?>
    <tr>
        <th><?=$words->getFormatted("Username") ?></th>
        <th><?=$words->getFormatted("Location") ?></th>
        <th><?=$words->getFormatted("ProfileSummary") ?></th>
    </tr>
<?php } ?>

<?php
for ($ii = 0; $ii < $iiMax; $ii++) {
    $m = $TMembers[$ii];
    ?>
    <tr class="<?=$styles[$ii%2] ?>">
        <td align="center">
            <a class="username" href="members/<?=$m->Username ?>"><?=$m->Username ?></a>
            <br />
            <?=MOD_layoutbits::PIC_50_50($m->Username) ?>
        </td>
        <td><?=$m->countryname ?></td>
        <td><?=$words->mTrad($m->ProfileSummary) ?></td>
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

  if (!APP_User::login()) {
     echo "<p>",$words->getFormatted("OnlinePrivateProfilesAreNotDisplayed"), "</p>\n";
  }
?>
