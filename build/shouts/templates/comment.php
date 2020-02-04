<?php
/**
 * defined vars:
 * $comment     - the comment object to display.
 * $count       - index of this comment.
 */
?>
<div class="comment" id="c<?=$comment->shout_id?>">
<table>
<tr>
<td valign="top" width="60px">
    <?php if ($lastHandle !== $comment->username) {
        echo MOD_layoutbits::PIC_50_50($comment->username,'',$style='');
    }
    ?>
</td>
<td valign="top">
    <h4><?=htmlentities($comment->title, ENT_COMPAT, 'utf-8')?></h4>
    <div class="text"><?=nl2br($comment->text)?></div>
    <div class="author small grey">
    <span title="<?=date($format['short'], $comment->unix_created)?>"><?php echo MOD_layoutbits::ago($comment->unix_created)?></span> <?php echo $words->getFormatted('by'); ?> <a href="user/<?=$comment->username?>"><?=$comment->username?></a>
    </div>
</td>
</tr>
</table>
</div>
