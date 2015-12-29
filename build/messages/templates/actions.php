<p>
    <button type="submit" name="submit_multi" value="delete" class="btn"
    onclick="return confirm ('<?php echo $words->getBuffered ('MessagesWarningConfirmDelete'); ?>')" >
    <?=$words->get('delmessage')?></button>
    <?php echo $words->flushBuffer();?>
    <?php if ($message_page == 'spam') { ?>
    <button type="submit" name="submit_multi" value="nospam" class="btn"><?=$words->get('marknospam')?></button>
    <?php } elseif ($message_page != 'sent' && $message_page != 'drafts') { ?>
    <button type="submit" name="submit_multi" value="markasspam" class="btn"><?=$words->get('markspam')?></button>
    <?php } else { ?>
    <?php }  ?>
</p>
