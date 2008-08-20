<?php ?>

<div class="info">
<p>Here you can send us feedback.<br />

      If required, one of our volunteers will answer you.<br />
<br />
Please have a look at our <a href="about/faq">FAQs</a> where many answers can be found.<br />
<br />
To help us please choose the feedback category carefully.<br />
</p>
<form action="about/feedback" method="post">
  <h4>Choose your feedback category</h4>
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
<h4>Your question </h4><p><textarea name="FeedbackQuestion" cols="40" rows="9"></textarea></p>

      <p><input type="checkbox" id="feedbackUrgent" name="urgent"> <label for="feedbackUrgent">Urgent!</label></p>
      <p><input type="checkbox" id="feedbackAnswerneeded" name="answerneeded"> <label for="feedbackAnswerneeded">I would like to get an answer, please.</label></p>
      <p><input type="submit" id="submit" name="submit" value="submit"></p>

<input name="action" type="hidden" value="ask">
</form></div>

