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

$list=$this->_data ; // Retrieve the data to display (set by the controller) 

$words = new MOD_words();
$styles = array( 'highlight', 'blank' ); // alternating background for table rows
$iiMax = count($list) ; // This retrieve the number of polls
?>

<table class="full">

<?php if ($list != false) { ?>
    <tr>
        <th><?=$words->getFormatted("polls_title") ?></th>
        <th><?=$words->getFormatted("poll_creator") ?></th>
        <th><?=$words->getFormatted("poll_NbContributors") ?></th>
        <th><?=$words->getFormatted("poll_status") ?></th>
        <th>Action</th>
    </tr>
<?php } ?>

<?php
for ($ii = 0; $ii < $iiMax; $ii++) {
    $p = $list[$ii];
    ?>
    <tr class="<?=$styles[$ii%2] ?>">
        <td align=left><b><? echo $words->fTrad($p->Title),"</b>"; echo "<br /><i>",$words->fTrad($p->Description),"</i>"; ?></td>
        <td align="left">
						<? 
						if (!empty($p->IdCreator)) {
							echo MOD_layoutbits::PIC_50_50($p->CreatorUsername) ;
							echo "<br />" ;
							echo "<a class=\"username\" href=\"bw/member.php?cid=",$p->CreatorUsername,"\">",$p->CreatorUsername,"</a>" ;
						} 
						if (!empty($p->IdGroupCreator)) {
							echo $words->getFormatted("Group"),":","<a  href=\"bw/groups.php?action=ShowMembers&IdGroup=",$p->IdGroupCreator,"\">",$p->GroupCreatorName,"</a>" ;
						} 
						?>
        </td>
        <td align=left><? echo $p->NbContributors; ?></td>
        <td align=left><? echo $p->Status; ?></td>
        <td align=left><? echo $p->PossibleActions; ?></td>
    </tr>
    <?php
}
?>
</table>
