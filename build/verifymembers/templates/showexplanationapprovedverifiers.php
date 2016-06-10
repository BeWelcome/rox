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

$words = new MOD_words($this->getSession());
$styles = array( 'highlight', 'blank' ); // alternating background for table rows
$iiMax = count($list) ; // This retrieve the list of the verifierd
$purifier = MOD_htmlpure::getBasicHtmlPurifier();
?>
<p>
<?=$words->getFormatted("verifymembers_approvedverifiersexp",$words->getFormatted("verifymembers_VerifiedByApproved"),$words->getFormatted("verifymembers_VerifiedByVerified"),$words->getFormatted("verifymembers_VerifiedByNormal")) ?>
</p>

<table class="full">

<?php if ($list != false) { ?>
    <tr align="left">
        <th></th>
        <th><?=$words->getFormatted("Username") ?></th>
        <th><?=$words->getFormatted("Location") ?></th>
        <th><?=$words->getFormatted("MemberSince") ?></th>
        <th><?=$words->getFormatted("ProfileSummary") ?></th>
    </tr>
<?php } ?>

<?php
for ($ii = 0; $ii < $iiMax; $ii++) {
    $m = $list[$ii];
    ?>
    <tr class="<?=$styles[$ii%2] ?>">
        <td align="left">
            <?=MOD_layoutbits::PIC_50_50($m->Username) ;?>

        </td>
        <td align="left">
            <a class="username" href="members/<?=$m->Username ?>"><?=$m->Username ?></a>
            <br /><?=$m->FullName ?>
            <br /><?=$m->age ?>
        </td>
        <td><?=$m->country ?><br/><?=$m->city ?></td>
        <td>
            <?=$words->getFormatted("MemberSinceColumn", MOD_layoutbits::ago(strtotime($m->created)))?>
        </td>
        <td>
            <a href="members/<?=$m->Username ?>/comments"><?=$words->getFormatted("ViewComments")."(".$m->NbComments.")"?></a><br/>
            <?php echo $purifier->purify(stripslashes($words->mInTrad($m->ProfileSummary, $language_id=0, true))); ?>
        </td>
    </tr>
    <?php
}
?>
</table>

