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

$words = $this->getWords();

$words = new MOD_words();
$styles = array( 'highlight', 'blank' ); // alternating background for table rows
$iiMax = count($list) ; // This retrieve the list of the verifierd
?>
<p>
<?=$words->getFormatted("verifymembers_approvedverifiersexp") ?>
</p>
<p>

<table class="full">

<?php if ($list != false) { ?>
    <tr>
        <th><?=$words->getFormatted("Username") ?></th>
        <th><?=$words->getFormatted("Location") ?></th>
    </tr>
<?php } ?>

<?php
for ($ii = 0; $ii < $iiMax; $ii++) {
    $m = $list[$ii];
    ?>
    <tr class="<?=$styles[$ii%2] ?>">
        <td align="center">
            <?=MOD_layoutbits::PIC_50_50($m->Username) ;?>
            <br />
            <a class="username" href="bw/member.php?cid=<?=$m->Username ?>"><?=$m->Username ?></a>
        </td>
        <td><?=$m->CountryName ?>/<?=$m->CityName ?></td>
    </tr>
    <?php
}
?>
</table>
</p>
