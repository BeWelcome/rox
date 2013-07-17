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

author JeanYves
This is the list pof pending reports
*/
// get current request
$request = PRequest::get()->request;

if (!isset($vars['errors']) || !is_array($vars['errors'])) {
    $vars['errors'] = array();
}

$list=$DataPost ; // Retrieve the data to display (set by the controller)

$words = new MOD_words();
$styles = array( 'highlight', 'blank' ); // alternating background for table rows
$iiMax = count($list) ; // This retrieve the number of polls
?>

<table class="full">

<?php if ($list != false) { ?>
    <tr>
        <th>Status</th>
        <th>Author</th>
        <th>Title and text</th>
        <th>Updated</th>
        <th>Action</th>
    </tr>
<?php } ?>

<?php
for ($ii = 0; $ii < $iiMax; $ii++) {
    $p = $list[$ii];
    ?>
    <tr class="<?=$styles[$ii%2] ?>">
        <td>
            <h4><?=$p->Status ?></h4>
        </td>
        <td>
            <?
            if (!empty($p->IdReporter)) {
                echo MOD_layoutbits::PIC_50_50($p->Username) ;
                echo "<br />" ;
                echo "<a class=\"username\" href=\"members/",$p->Username,"\">",$p->Username,"</a>" ;
            }
            ?>
        </td>
        <td><? echo "<b>",$p->PostComment,"</b></td>" ; ?>
        <td>
		<? echo "<a href='forums/reporttomod/",$p->IdPost,"/".$p->IdReporter."'>view report</a>" ;?>
		</td>
    </tr>
    <?php
}
?>
</table>
