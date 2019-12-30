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
$IdCategory = '11';
$RequestURI = '';
$redirect = '';
$data = '';
if (isset($_GET['IdCategory']) && $_GET['IdCategory']) $IdCategory = $_GET['IdCategory'];
if (isset($_GET['RequestURI']) && $_GET['RequestURI']) $RequestURI = $_GET['RequestURI'];
if (isset($_GET['redirect']) && $_GET['redirect']) {
    $redirect = $_GET['redirect'];
}
if (isset($_GET['data']) && $_GET['data']) {
    $data = $_GET['data'];
}

$mem = $this->getRedirectedMem('post');
if(isset($mem['IdCategory'])) $IdCategory = $mem['IdCategory'];
if(isset($mem['RequestURI'])) $RequestURI = $mem['RequestURI'];
if (isset($mem['redirect'])) {
    $redirect = $mem['redirect'];
}
if (isset($mem['data'])) {
    $data = $mem['data'];
}
$FeedbackQuestion = isset($mem['FeedbackQuestion']) ? $mem['FeedbackQuestion'] : '';
$FeedbackEmail = isset($mem['FeedbackEmail']) ? $mem['FeedbackEmail'] : '';
$answernotneeded = isset($mem['answernotneeded']) ? $mem['answernotneeded'] : null;
$errors = $this->getRedirectedMem('errors');

if ($errors = $this->getRedirectedMem('errors'))
{
    foreach ($errors as $error)
    {
        echo '<div class="col-12 alert alert-danger">' . $words->get($error) . '</div>';
    }
}
?>
<div class="row">
<div class="col-12 col-md-6">
<p><?php echo $words->get("FeedBackDisclaimer") ?></p>
</div>

<div class="col-12 col-md-6">

<form action="about/feedback" method="post">
    <?=$callback_tag ?>
    <input type="hidden" name="RequestURI" value="<?= htmlspecialchars($RequestURI, ENT_QUOTES) ?>">
    <input type="hidden" name="redirect" value="<?php echo htmlentities($redirect); ?>">
    <input type="hidden" name="data" value="<?php echo htmlentities($data); ?>">

    <div class="form-group">
        <label for="IdCategory"><?php echo $words->get("FeedBackChooseYourCategory")?></label>
        <select class="form-control select2" data-minimum-results-for-search="Infinity" id="IdCategory" name="IdCategory">
            <?php foreach ($categories as $cat) { ?>
                <option value="<?php echo $cat->id ?>" <?=($cat->id == $IdCategory) ? 'selected="selected"': '' ?>>
                    <?php echo $words->getSilent("FeedBackName_" . $cat->name) ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <?php echo $words->flushBuffer(); ?>
    <!-- type-select -->

    <?php
        if (in_array('FeedbackErrorDataMissing', $errors))
        {
            echo '<div class="alert alert-danger">';
            foreach ($errors as $error)
            {
                echo $words->get($error) . '<br>';
            }
            echo '</div>';
        }
        ?>

    <div class="form-group">
        <label for="FeedbackQuestion"><?php echo $words->get("FeedBackEnterYourQuestion")?></label>
        <textarea id="FeedbackQuestion" name="FeedbackQuestion" class="form-control w-100" rows="9"><?php echo $FeedbackQuestion;?></textarea>
    </div> <!-- type-text -->

    <?php if (!$this->model->getLoggedInMember()) : ?>
    <div class="type-text <?php
        if (in_array('FeedbackErrorBadEmail', $errors))
        {
            echo "error \">";
            foreach ($errors as $error)
            {
                echo "<strong class=\"message\">{$words->get($error)}</strong>";
            }
        }
        else echo " \">";
        ?>
    </div>
    <div class="form-group">
        <label for="FeedbackEmail"><?php echo $words->get("FeedBackEmail")?></label>
        <input class="form-control" type="text" id="FeedbackEmail" name="FeedbackEmail" value="<?php echo $FeedbackEmail;?>"/>
    </div>
    <?php endif; ?>

    <div class="type-check form-group">
        <p><input type="checkbox" id="feedbackAnswernotneeded" name="answernotneeded" <?php if ($answernotneeded) echo "checked='checked'";?>/> <label for="feedbackAnswernotneeded"> <?php echo $words->get("FeedBackIDoNotWantAnAnswer")?></label></p>
    </div> <!-- type-check -->

    <div>
        <input type="submit" class="btn btn-primary" id="submit" name="submit" value="<?php echo $words->getSilent("FeedbackSubmit")?>" /><?php echo $words->flushBuffer(); ?>
        <input name="action" type="hidden" value="ask" />
    </div>
</form>
</div>
</div>
