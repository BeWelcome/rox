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
        <th>status</th>
        <th>author</th>
        <th>title and text</th>
        <th>locations</th>
        <th>Action</th>
    </tr>
<?php } ?>

<?php
for ($ii = 0; $ii < $iiMax; $ii++) {
    $p = $list[$ii];
	$ChosenLocations=$p->ChosenLocations ;
    ?>
    <tr class="<?=$styles[$ii%2] ?>">
        <td>
            <h4><?=$p->Status ?></h4>
        </td>
        <td>
            <?
            if (!empty($p->IdCreator)) {
                echo MOD_layoutbits::PIC_50_50($p->CreatorUsername) ;
                echo "<br />" ;
                echo "<a class=\"username\" href=\"bw/member.php?cid=",$p->CreatorUsername,"\">",$p->CreatorUsername,"</a>" ;
				echo "<br/><i>",$p->PurposeDescription,"</i>" ;
            }
            ?>
        </td>
        <td><? echo "<b>",$words->fTrad($p->IdTitleText),"</b><br/>",$words->fTrad($p->IdMessageText); ?></td>
        <td>
<?php
	foreach ($ChosenLocations as $loc) {
?>
	<?=$loc->Choice?><br />
<?php
	}
?>
		</td>
        <td>
		<?php
		// Local Vol coord with All right can modify the message parameters
		// Owner can modify it too if it is in the ToApproveSTatus
		if ((($_SESSION["IdMember"]==$p->IdCreator)and($p->Status=='ToApprove')) or (MOD_right::get()->HasRight("ContactLocation","All"))) {
			echo "<a href=\"contactlocal/modify/".$p->IdMess."\">Modify</a>" ;
		}
		// Local Vol coord with All right can delete the message
		// Owner can delete it too if it is in the ToApproveSTatus
		if ((($_SESSION["IdMember"]==$p->IdCreator)and($p->Status=='ToApprove')) or (MOD_right::get()->HasRight("ContactLocation","All"))) {
			echo "<br /><a href=\"contactlocal/delete/".$p->IdMess."\" onclick=\"return confirm('Are you sure you want to delete this message ?')\">Delete</a>" ;
		}
		// Local Vol coord with All right can approve the message
		// Owner can approve it too if it is in the ToApproveSTatus and if he has scope CanApprove
		if ((($_SESSION["IdMember"]==$p->IdCreator)and($p->Status=='CanTrigger')and (MOD_right::get()->HasRight("ContactLocation","CanTrigger"))) or (MOD_right::get()->HasRight("ContactLocation","All"))) {
			echo "<br /><a href=\"contactlocal/delete/".$p->IdMess."\" onclick=\"return confirm('Are you sure you want to delete this message ?')\">Delete</a>" ;
		}
		?></td>
    </tr>
    <?php
}
?>
</table>
