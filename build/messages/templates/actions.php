
    <button type="submit" name="submit_multi" value="delete" class="button"><?=$words->get('delmessage')?></button>
    <?php if ($message_page == 'spam') { ?>
    <button type="submit" name="submit_multi" value="nospam" class="button"><?=$words->get('marknospam')?></button>
    <?php } elseif ($message_page != 'sent' && $message_page != 'drafts') { ?>
    <button type="submit" name="submit_multi" value="markasspam" class="button"><?=$words->get('markspam')?></button>
    <?php } else { ?>
    <?php }  ?>
