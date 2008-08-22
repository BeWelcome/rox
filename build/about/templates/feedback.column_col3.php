<?php ?>

<div class="info">

<?php echo $words->get("FeedBackDisclaimer") ?>

<form action="about/feedback/submit" method="post">
  <h4><label for="IdCategory"><?php echo $words->get("FeedBackChooseYourCategory")?></label></h4>
  <p><select id="IdCategory" name="IdCategory">
      <?php foreach ($categories as $cat) { ?>
<option value="<?php echo $cat->id ?>"><?php echo $words->get("FeedBackName_" . $cat->name) ?></option>
      <?php } ?>
</select>
</p>
      
<h4><label for="FeedbackQuestion"><?php echo $words->get("FeedBackEnterYourQuestion")?></label></h4>
<p><textarea id="FeedbackQuestion" name="FeedbackQuestion" cols="40" rows="9"></textarea></p>

      <p><input type="checkbox" id="feedbackUrgent" name="urgent"> <label for="feedbackUrgent">Urgent!</label></p>
      <p><input type="checkbox" id="feedbackAnswerneeded" name="answerneeded"> <label for="feedbackAnswerneeded">I would like to get an answer, please.</label></p>
      <p><input type="submit" id="submit" name="submit" value="submit"></p>

<input name="action" type="hidden" value="ask">
</form>
</div>

