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
$words = new MOD_words();
$IdCategory = '';
if (isset($_GET['IdCategory']) && $_GET['IdCategory']) $IdCategory = $_GET['IdCategory'];

$mem = $this->getRedirectedMem('post');
$IdCategory = isset($mem['IdCategory']) ? $mem['IdCategory'] : 1;
$FeedbackQuestion = isset($mem['FeedbackQuestion']) ? $mem['FeedbackQuestion'] : '';
$FeedbackEmail = isset($mem['FeedbackEmail']) ? $mem['FeedbackEmail'] : '';
$urgent = isset($mem['urgent']) ? $mem['urgent'] : null;
$answerneeded = isset($mem['answerneeded']) ? $mem['answerneeded'] : null;
?>

<p><?php echo $words->get("FeedBackDisclaimer") ?></p>

<form class="yform full" action="about/feedback" method="post">
    <?=$callback_tag ?>

    <div class="type-select">
        <h4><label for="IdCategory"><?php echo $words->get("FeedBackChooseYourCategory")?></label></h4>
        <select id="IdCategory" name="IdCategory">
            <?php foreach ($categories as $cat) { ?>
                <option value="<?php echo $cat->id ?>" <?=($cat->id == $IdCategory) ? 'selected="selected"': '' ?>>
                    <?php echo $words->get("FeedBackName_" . $cat->name) ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="type-text">
        <h4><label for="FeedbackQuestion"><?php echo $words->get("FeedBackEnterYourQuestion")?></label></h4>
        <textarea id="FeedbackQuestion" name="FeedbackQuestion" class="long" cols="60" rows="9"><?php echo $FeedbackQuestion;?></textarea>
    </div>

    <?php if (!$this->model->getLoggedInMember()) : ?>
    <div class="type-text <?php
        if ($errors = $this->getRedirectedMem('errors'))
        {
            echo "error";
        }
        ?>"><?php
        if ($errors = $this->getRedirectedMem('errors'))
        {
            foreach ($errors as $error)
            {
                echo "<strong class=\"message\">{$words->get($error)}</strong>";
            }
        }
    ?>

        <h4><label for="FeedbackEmail"><?php echo $words->get("FeedBackEmail")?></label></h4>
        <input type="text" id="FeedbackEmail" name="FeedbackEmail" value="<?php echo $FeedbackEmail;?>"/>
    </div>
    <?php endif; ?>

    <div class="type-check">
        <p><input type="checkbox" id="feedbackUrgent" name="urgent" <?php if ($urgent) echo "checked='checked'";?>/> <label for="feedbackUrgent"> <?php echo $words->get("FeedBackUrgentQuestion")?></label></p>
        <p><input type="checkbox" id="feedbackAnswerneeded" name="answerneeded" <?php if ($answerneeded) echo "checked='checked'";?>/> <label for="feedbackAnswerneeded"> <?php echo $words->get("FeedBackIWantAnAnswer")?></label></p>
    </div>

    <div>
        <input type="submit" id="submit" name="submit" value="submit" />
        <input name="action" type="hidden" value="ask" />
    </div>
</form>

