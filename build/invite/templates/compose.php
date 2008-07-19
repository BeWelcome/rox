
<form method="POST" action="<?=$page_url ?>">
    <?=$callback_tag ?>
    <div class="row">
    <h3 class="borderless">
        Email: 
    </h3>
    <p>
        <input name="email" value="<?=$email?>" size="80" />
    </p>
    <?php if (isset($problems['email']) && $problems['email']) echo '<p class="small warning">'.$problems['email'].'</p>';?>
    <label class="small">Enter email addresses of your friends (seperated by commas)</label>
    </div>
    
    <div class="row">
    <h3 class="borderless">
        Subject:
    </h3>
    <p>
        <input name="subject" value="<?=$subject?>" size="80" />
    </p>
    </div>
    
    <div class="row">
    <h3 class="borderless">
        Text:
   </h3>
    <p>
        <textarea name="text" rows="10" cols="80"><?=$text ?></textarea>
    </p>
    <label class="small">
    Choose a different language in the footer to change the language of this basic text, too. Or modify it yourself.<br />
    </label>
    </div>
    
    <div class="row">
    <p>
        <input type="checkbox" name="attach_picture" id="JoinMemberPict"<?=$attach_picture ?>/>
        <label for="JoinMemberPict">Attach my profile picture</label>
    </p>
    </div>
    
    <p>
        <input type="submit" value="Send Invitation"/>
    </p>

</form>
        

