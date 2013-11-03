<p>
    <button type="submit" name="submit_multi" value="delete" class="button"
    onclick="return confirm ('<?php echo $words->getBuffered ('MessagesWarningConfirmDelete'); ?>')" >
    <span class="button_label"><?=$words->get('delmessage')?></span></button>
    <?php echo $words->flushBuffer();?>
    <?php if ($message_page == 'spam') { ?>
    <button type="submit" name="submit_multi" value="nospam" class="button"><span class="button_label"><?=$words->get('marknospam')?></span></button>
    <?php } elseif ($message_page != 'sent' && $message_page != 'drafts') { ?>
    <button type="submit" name="submit_multi" value="markasspam" class="button"><span class="button_label"><?=$words->get('markspam')?></span></button>
    <?php } else { ?>
    <?php }  ?>
</p>
