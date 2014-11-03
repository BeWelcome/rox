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
$memory_tag = '';
$page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);

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

    if ($member_data = $memory->member_data) {
        $this->member1->member_data = $member_data[1];
        $this->member2->member_data = $member_data[2];
    }
}
$memory->post = false;
$memory->problems = false;
$memory_tag = $formkit->setMemForRecovery($memory);

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

<p><?=$words->getFormatted("verifymembers_proceedexplanation",$this->member1->Username,$this->member2->Username) ?></p>


<form name="proceedtoverify" action="<?=$page_url?>"  id="idproceedtoverify" method="post">

    <?=$callback_tag?>
    <?=$memory_tag?>

    <div class="subcolumns" id="profile_subcolumns">

      <div class="c50l" >
        <div class="subcl" >

            <?php /*<input type="hidden" name="<?=$callbackId ?>"  value="1"/> */
            $m = $this->member1;
            $n = 1;
            require 'verifydata.template.php';
            ?>

        </div> <!-- subcl -->
      </div> <!-- c50l -->
      <div class="c50r" >
        <div class="subcr" >

            <?php /*<input type="hidden" name="<?=$callbackId ?>"  value="1"/> */
            $m = $this->member2;
            $n = 2;
            require 'verifydata.template.php';
            ?>

        </div> <!-- subcr -->
      </div> <!-- c50r -->

    </div> <!-- subcolumns -->

    <div class="bw-row">
        <input type="submit" class="button" value="<?=$words->getFormatted("verifymembers_proceedtocheck") ?>" />
    </div>

</form>


