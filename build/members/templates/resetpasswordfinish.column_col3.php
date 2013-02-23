<?php
    $words = $this->getWords();
    $UserNameOrEmail = "Test";
?><div class="info">
<p><?php echo $words->get("lostpasswordsent", $UserNameOrEmail); ?></p>
</div>