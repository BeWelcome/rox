<?php
    $words = $this->getWords();
    $layoutkit = $this->layoutkit;
    $formkit = $layoutkit->formkit;
    $callback_tag = $formkit->setPostCallback('MembersController', 'resetPasswordCallback');

    $errors = $this->getRedirectedMem('errors');
    if (!empty($errors))
    {
        echo '<div class="error">';
        echo $words->get($errors[0]);
        echo "</div>";
    }
?>
<div class="info">
<p><?php echo $words->get("localpasswordrule"); ?></p>
<form method="post" action="resetpassword">
<?php echo $callback_tag; ?>
<input type="hidden" name="action" value="sendpassword">
<label for="usernameOrEmail"><?php echo $words->get("UserNameOrEmail"); ?></label><br />
<br />
<input type="text" name="usernameOrEmail" id="usernameOrEmail" size="40" >
<input type="submit" class="button" id="submit" name="submit" value="<?php echo $words->getBuffered("submit"); ?>"><?php echo $words->flushBuffer();?>
</form>
</div>
