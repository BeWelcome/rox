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

$list = $this->_data; // Retrieve the data to display (set by the controller)

$iiMax = 0;
if (!empty($list)) {
    $iiMax = count($list);
}
$words = new MOD_words();
?>
<table class="table table-striped table-hover">
    <tr>
        <th scope="col"><?= $words->getFormatted("polls_title") ?></th>
        <th scope="col"><?= $words->getFormatted("poll_creator") ?></th>
        <th scope="col"><?= $words->getFormatted("poll_NbContributors") ?></th>
        <th scope="col"><?= $words->getFormatted("poll_status") ?></th>
        <th scope="col">Action</th>
    </tr>
    <?php
    if ($iiMax == 0) {
        ?>
        <tr>
            <td colspan="5"><?= $words->get('polls_no_polls'); ?></td>
        </tr>
        <?php
    }
    for ($ii = 0; $ii < $iiMax; $ii++) {
        $p = $list[$ii];
        ?>
        <tr>
            <td class="text-wrap text-break">
                <p><strong><?php echo $words->fTrad($p->Title); ?></strong><br>
                    <?php if (null !== $p->GroupId) {
                        echo $words->getFormatted("Group"), ": ", "<a  href=\"group/", $p->GroupId, "\">", $p->GroupName, "</a><br><br>";
                    }
                    echo $this->_purifier->purify($words->fTrad($p->Description)); ?></p>
            </td>
            <td>
                <?php
                if (!empty($p->IdCreator)) {
                    echo MOD_layoutbits::PIC_50_50($p->CreatorUsername);
                    echo "<br />";
                    echo "<a class=\"username\" href=\"member/", $p->CreatorUsername, "\">", $p->CreatorUsername, "</a>";
                }
                ?>
            </td>
            <td><?php echo $p->NbContributors; ?></td>
            <td><?php echo $p->Status; ?></td>
            <td><?php if (isset($p->PossibleActions)) { echo $p->PossibleActions; }?></td>
        </tr>
        <?php
    }
    ?>
</table>
