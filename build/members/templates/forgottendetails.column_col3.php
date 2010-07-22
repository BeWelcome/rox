<?php
    $errors = $this->getRedirectedMem('errors');
    if (!empty($errors))
    {
        echo '<p class="error">';
        foreach ($errors as $error)
        {
            echo $words->get($error);
        }
        echo '</p>';
    }
?>
<form action="" method="post">
    <p><?php echo $words->get('localpasswordrule');?></p>
    <?php echo $this->getCallbackOutput('MembersController', 'forgottenCallback');?>
    <input type="text" name="email" size="25"/>
    <input type="submit" value="<?php echo $words->get('RetrievePassword');?>"/>
</form>
