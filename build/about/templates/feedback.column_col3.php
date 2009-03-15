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
?>

<?php echo $words->get("FeedBackDisclaimer") ?>

<form action="about/feedback/submit" method="post">
    <h4><label for="IdCategory"><?php echo $words->get("FeedBackChooseYourCategory")?></label></h4>
    <p>
        <select id="IdCategory" name="IdCategory">
            <?php foreach ($categories as $cat) { ?>
            <option value="<?php echo $cat->id ?>"><?php echo $words->get("FeedBackName_" . $cat->name) ?></option>
            <?php } ?>
        </select>
    </p>
      
    <h4><label for="FeedbackQuestion"><?php echo $words->get("FeedBackEnterYourQuestion")?></label></h4>
    <p><textarea id="FeedbackQuestion" name="FeedbackQuestion" class="long" cols="60" rows="9"></textarea></p>
    <p><input type="checkbox" id="feedbackUrgent" name="urgent" /> <label for="feedbackUrgent"> <?php echo $words->get("FeedBackUrgentQuestion")?></label></p>
    <p><input type="checkbox" id="feedbackAnswerneeded" name="answerneeded" /> <label for="feedbackAnswerneeded"> <?php echo $words->get("FeedBackIWantAnAnswer")?></label></p>
    <p><input type="submit" id="submit" name="submit" value="submit" /></p>

    <input name="action" type="hidden" value="ask">
</form>

