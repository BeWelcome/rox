<div class="w-100 my-2">
    <button type="submit" name="submit_multi" value="delete" class="btn btn-danger"
    onclick="return confirm ('<?php echo $words->getBuffered ('MessagesWarningConfirmDelete'); ?>')" >
        <i class="fa fa-trash-alt mr-1"></i><?=$words->get('delmessage')?></button>
    <?php echo $words->flushBuffer();?>
    <?php if ($message_page == 'spam') { ?>
    <button type="submit" name="submit_multi" value="nospam" class="btn btn-secondary"><?=$words->get('marknospam')?></button>
    <?php } elseif ($message_page != 'sent' && $message_page != 'drafts') { ?>
    <button type="submit" name="submit_multi" value="markasspam" class="btn btn-secondary"><?=$words->get('markspam')?></button>
    <?php } else { ?>
    <?php }  ?>
</div>
