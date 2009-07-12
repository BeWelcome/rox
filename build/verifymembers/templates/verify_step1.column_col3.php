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
$layoutbits = new MOD_layoutbits();

if (!empty($errormessage)) {
    echo "
    <p><b>$errormessage</b></p>";
}

?>
<p>
<?=$words->getFormatted("verifymembers_explanation",$_SESSION["Username"]) ?>
</p>
<p>
    <form name="entermembertoverify" action="verifymembers/prepareverifymember"  id="prepareverifymember" method="post">
    
    <div class="subcolumns" id="profile_subcolumns">

      <div class="c50l" >
        <div class="subcl" >

            <?php /*<input type="hidden" name="<?=$callbackId ?>"  value="1"/> */ 
            $m = $this->member1;
            $verification_status = $m->verification_status;
            if ($verification_status) $verification_text = $words->getSilent('verifymembers_'.$verification_status);
            ?>

            <div class="row">
                <table>
                    <tr>
                        <td><?php echo MOD_layoutbits::PIC_50_50($m->Username,'',$style='framed') ?></td>
                        <td>
                            <?php echo '<a href="people/'.$m->Username.'">'.$m->Username.'</a>' ?>
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
            <div class="row">
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

            <div class="row">
                <table>
                    <tr>
                        <td><?php echo MOD_layoutbits::PIC_50_50($m->Username,'',$style='framed') ?></td>
                        <td>
                            <?php echo '<a href="people/'.$m->Username.'">'.$m->Username.'</a>' ?>
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
            <div class="row">
            <label for="password2"><?=$words->getFormatted("verifymembers_member_pw", $m->Username) ?></label>
            <input type="password"  name="password2"><br />
            </div>

        </div> <!-- subcr -->
      </div> <!-- c50r -->

    </div> <!-- subcolumns -->

    <input  type="submit" value="<?=$words->getFormatted("verifymembers_proceedtocheck") ?>">

</form>
</p>

