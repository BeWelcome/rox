<?php ?>

<div class="info">

<?php echo $words->get("FeedBackDisclaimer") ?>

<form action="about/feedback/submit" method="post">
  <h4><?php echo $words->get("FeedBackChooseYourCategory")?></h4>
  <p><select name="IdCategory">

<option value=1>Bug problem</option>
<option value=2>Abuse report</option>
<option value=4>Comment problem</option>
<option value=5>Media issue</option>
<option value=6>Forum Issue</option>
<option value=7>Suggestion</option>
<option value=8>I want to help!</option>
</select>
</p>
      
<h4><?php echo $words->get("FeedBackEnterYourQuestion")?></h4>
<p><textarea name="FeedbackQuestion" cols="40" rows="9"></textarea></p>

      <p><input type="checkbox" id="feedbackUrgent" name="urgent"> <label for="feedbackUrgent">Urgent!</label></p>
      <p><input type="checkbox" id="feedbackAnswerneeded" name="answerneeded"> <label for="feedbackAnswerneeded">I would like to get an answer, please.</label></p>
      <p><input type="submit" id="submit" name="submit" value="submit"></p>

<input name="action" type="hidden" value="ask">
</form>
</div>

