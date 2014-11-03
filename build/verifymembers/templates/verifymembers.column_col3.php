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
$ww = $words;
$layoutkit = $this->layoutkit;
$formkit = $layoutkit->formkit;
$callback_tag = $formkit->setPostCallback('VerifymembersController', 'checkPasswordCallback');

$baseuri = PVars::getObj('env')->baseuri;
if (PVars::getObj('env')->force_ssl_sensitive){
    $baseuri = PVars::getObj('env')->baseuri_https;
}

$page_url = $baseuri . implode('/', PRequest::get()->request);

$action_url = $page_url;

if (!empty($_SERVER['QUERY_STRING'])) {
    $action_url .= '?'.$_SERVER['QUERY_STRING'];
}

if (!empty($errormessage)) {
    echo "
    <p class=\"error\">$errormessage</p>";
}

if (!$memory = $formkit->getMemFromRedirect()) {
    // no memory
    // echo 'no memory';
} else {
    // from previous form
    if ($memory->post) {
        $post = $memory->post;
        foreach ($post as $key => $value) {
            $vars[$key] = $value;
        }
    }
}

// Check for errors and update status and display a message
if (isset($vars['errors']) and count($vars['errors']) > 0) {
    echo '<div class="error">'.$ww->FormError.'<br/>';
    foreach ($vars['errors'] as $error)
    {
        echo $words->get($error) ."<br/>";
    }
    echo "</div>";
} else {
    if ($this->status == 'proceed') {
          echo '<div class="note check">'.$ww->VerifyProceed.'</div>';
    }
    $vars['errors'] = array();
}
?>
<p>
<?=$words->getFormatted("verifymembers_explanation",$_SESSION["Username"]) ?>
</p>

<form name="entermembertoverify" action="<?=$action_url?>" id="prepareverifymember" method="post">

        <?=$callback_tag?>
    
    <div class="subcolumns" id="profile_subcolumns">

      <div class="c50l" >
        <div class="subcl" >

            <div class="box-bg">
            <div class="box">
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

            <input type="hidden" name="cid1" value="<?=$m->id?>" />
            <input type="hidden" name="username1" value="<?=$m->Username?>" />
            <div class="bw-row">
            <label for="password1"><?=$words->getFormatted("verifymembers_member_pw", $m->Username) ?></label>
            <input id="password1" type="password"  name="password1" />
            </div>
           </div>
           </div>
        </div> <!-- subcl -->
      </div> <!-- c50l -->
      <div class="c50r" >
        <div class="subcr" >
            <div class="box-bg">
            <div class="box">
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

            <input type="hidden" name="cid2" value="<?=$m->id?>" />
            <input type="hidden" name="username2" value="<?=$m->Username?>" />
            <div class="bw-row">
            <label for="password2"><?=$words->getFormatted("verifymembers_member_pw", $m->Username) ?></label>
            <input id="password2" type="password"  name="password2" />
            </div>
           </div>
           </div>
        </div> <!-- subcr -->
      </div> <!-- c50r -->

    </div> <!-- subcolumns -->

    <input  type="submit" value="<?=$words->getFormatted("verifymembers_proceedtocheck") ?>" />

</form>


