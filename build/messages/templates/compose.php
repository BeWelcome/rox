


<h3>Message to <a href="bw/member.php?cid=<?=$receiver_username ?>"><?=$receiver_username ?></a></h3>

<form method="POST" action="<?=$page_url ?>">
    <?=$callback_tag ?>
    
    <?php if ($receiver_username) { ?>
    <input type="hidden" name="receiver_id" value="<?=$receiver_id ?>"/>
    <?php } else { ?>
    <p>To: <input name="receiver_username"/></p>
    <?php } ?>
    
    <p>
        <textarea name="text" rows="15" cols="80"><?=$text ?></textarea>
    </p>
    
    <p>
        I confirm that I have read the
        <a href="http://www.bevolunteer.org/wiki/Spam_Info_Page">Infos about Spam</a>
        and agree with them.
    </p>
    
    <p>
        <input type="checkbox" name="agree_spam_policy" id="IamAwareOfSpamCheckingRules">
        <label for="IamAwareOfSpamCheckingRules">I agree</label>
    </p>
    
    <p>
        <input type="checkbox" name="attach_picture" id="JoinMemberPict"<?=$attach_picture ?>/>
        <label for="JoinMemberPict">Attach my profile picture</label>
    </p>
    
    <p>
        <input type="submit" value="send"/>
    </p>

</form>
        

