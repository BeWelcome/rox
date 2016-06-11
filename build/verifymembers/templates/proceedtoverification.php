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
$layoutkit = $this->layoutkit;
$formkit = $layoutkit->formkit;
$callback_tag = $formkit->setPostCallback('VerifymembersController', 'verifyCallback');

$page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);

if (!empty($errormessage)) {
    echo "
    <p><b>$errormessage</b></p>";
}

?>
<p>
<?=$words->getFormatted("verifymembers_proceedexplanation",$this->_session->get("Username"),$m->Username) ?>
</p>
<p>
<form name="proceedtoverify" action="verify/<?=$this->member2?>/proceed"  id="idproceedtoverify" method="post">

    <?=$callback_tag?>
    <input type="hidden" name="IdMemberToVerify"  value="<?=$m->id ?>"/>

    <table border="0">
    <tr><td align=center>
             <?  MOD_layoutbits::PIC_50_50($m->Username); ?>
    </td></tr>
    <tr><td>
        <?=$words->getFormatted("verifymembers_name_to_check", $m->FirstName, "<i>".$m->SecondName."</i>", $m->LastName) ?>
    </td></tr>
    <tr><td>
        <?=$words->getFormatted("verifymembers_address_to_check", $m->HouseNumber, $m->StreetName,$m->Zip, $m->CityName) ?>
    </td></tr>
    <tr><td>
        <input type="checkbox" name="NameConfirmed">
        <?=$words->getFormatted("verifymembers_IdoConfirmTheName", $m->Username) ?>
    </td></tr>
    <tr><td>
        <input type="checkbox" name="AddressConfirmed">
        <?=$words->getFormatted("verifymembers_IdoConfirmTheAdress", $m->Username) ?>
    </td></tr>
    <tr><td align="left">
        <?=$words->getFormatted("verifymembers_Comment") ?>
        <br>
        <textarea name="comment" cols="50" rows="5"></textarea>
    </td></tr>
    <tr><td align="center">
        <input type="submit" class="button" value="<?=$words->getFormatted("verifymembers_proceedtocheck") ?>">
    </td></tr>
    </table>

    <div class="subcolumns" id="profile_subcolumns">

      <div class="c50l" >
        <div class="subcl" >

            <?php /*<input type="hidden" name="<?=$callbackId ?>"  value="1"/> */
            $m = $this->member1;
            $verification_status = $m->verification_status;
            if ($verification_status) $verification_text = $words->getSilent('verifymembers_'.$verification_status);
            ?>

            <div class="bw-row">
                <table>
                    <tr>
                        <td><?php echo MOD_layoutbits::PIC_50_50($m->Username,'',$style='framed') ?></td>
                        <td>
                            <?php echo '<a href="members/'.$m->Username.'">'.$m->Username.'</a>' ?>
                            <?=($verification_status) ? '<img src="images/icons/shield.png" alt="'.$verification_text.'" title="'.$verification_text.'">': ''?>
                            <br />
                            <?php echo $m->country; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- The following will disable the nasty PPostHandler -->
            <input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>
            <input type="hidden" name="username1" value="<?=$m->Username?>">
            <div class="bw-row">
            <label for="password1"><?=$words->getFormatted("verifymembers_member_pw", $m->Username) ?></label>
            <input type="password"  name="password1">
            </div>

        </div> <!-- subcl -->
      </div> <!-- c50l -->
      <div class="c50r" >
        <div class="subcr" >

            <?php /*<input type="hidden" name="<?=$callbackId ?>"  value="1"/> */
            $m = $this->member2;
            $verification_status = $m->verification_status;
            if ($verification_status) $verification_text = $words->getSilent('verifymembers_'.$verification_status);
            ?>

            <div class="bw-row">
                <table>
                    <tr>
                        <td><?php echo MOD_layoutbits::PIC_50_50($m->Username,'',$style='framed') ?></td>
                        <td>
                            <?php echo '<a href="members/'.$m->Username.'">'.$m->Username.'</a>' ?>
                            <?=($verification_status) ? '<img src="images/icons/shield.png" alt="'.$verification_text.'" title="'.$verification_text.'">': ''?>
                            <br />
                            <?php echo $m->country; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- The following will disable the nasty PPostHandler -->
            <input type="hidden" name="PPostHandlerShutUp" value="ShutUp"/>
            <input type="hidden" name="username2" value="<?=$m->Username?>">
            <div class="bw-row">
            <label for="password2"><?=$words->getFormatted("verifymembers_member_pw", $m->Username) ?></label>
            <input type="password"  name="password2"><br />
            </div>

        </div> <!-- subcr -->
      </div> <!-- c50r -->

    </div> <!-- subcolumns -->

</form>
</p>

