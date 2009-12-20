<?
/**
 * defined vars:
 * $comment     - the comment object to display.
 * $count       - index of this comment.
 */
?>
<div class="comment_compact" id="c<?=$comment->shout_id?>">
<table>
<tr>
<td valign="top" width="30px">
    <?php if ($lastHandle !== $comment->username) {
        echo MOD_layoutbits::PIC_30_30($comment->username,'',$style='');
    }
    ?>
</td>
<td valign="top">
    <div class="text"><?=trim($comment->text)?></div>
    <div class="small grey">
    <span title="<?=date($format['short'], $comment->unix_created)?>"><?php echo MOD_layoutbits::ago($comment->unix_created)?></span> <?php echo $words->getFormatted('by'); ?> <a href="user/<?=$comment->username?>"><?=$comment->username?></a>
    </div>
</td>
</tr>
</table>
</div>
