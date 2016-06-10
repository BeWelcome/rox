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
$iiMax = count($list) ; // This retrieve the list of the verifiers
?>

<p><?=$words->getFormatted("MemberHasVerificationLevel","<a class=\"username\" href=\"members/".htmlspecialchars($this->VerifierUsername, ENT_QUOTES)."\">".htmlspecialchars($this->VerifierUsername, ENT_QUOTES)."</a>", "<strong>".$words->getFormatted($VerificationMaxLevel)."</strong>") ?></p>

<table class="full">
<?php if ($list != false) { ?>
    <tr>
        <th align="center"><?=$words->getFormatted("Username") ?></th>
        <th align="center"><?=$words->getFormatted("Location") ?></th>
        <th align="center"><?=$words->getFormatted("verifymembers_HasCheckName") ?></th>
        <th align="center"><?=$words->getFormatted("verifymembers_HasCheckAddress") ?></th>
        <th align="center"><?=$words->getFormatted("verifymembers_CommentOfVer") ?></th>
        <th align="center"><?=$words->getFormatted("verifymembers_Type") ?></th>
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
        <td align="center"><?=$m->CityName ?></td>
        <td align="center"><? if ($m->NameVerified=="True") {
                                   echo $words->getFormatted("Yes") ;
                                }
                                else {
                                   echo $words->getFormatted("No") ;
                                }?>
         </td>
        <td align="center"><? if ($m->AddressVerified=="True") {
                                   echo $words->getFormatted("Yes") ;
                                }
                                else {
                                   echo $words->getFormatted("No") ;
                                }?>
         </td>
        <td align="left">

        <? echo strftime('%d/%m/%Y',strtotime($m->VerificationDate)) ; ?>:<br />
        <? echo $m->Comment; ?></td>
        <td align="left"><?=$words->getFormatted("verifymembers_".$m->VerificationType) ; ?></td>
    </tr>
    <?php
}
?>
</table>
