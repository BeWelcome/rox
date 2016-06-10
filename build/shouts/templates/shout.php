<?
/**
 * defined vars:
 * $comment     - the comment object to display.
 * $count       - index of this comment.
 */
if (!isset($headingLevel)) {
  $headingLevel = 4;
}
$words = new MOD_words($this->getSession());
$shouts = new ShoutsController();

?>

    <p class="action">
<?php
if (!$shouts->getShouts($application,$id)) {
?>
    <div class="comment" id="c<?=$comment->shout_id?>">
    <table>
    <tr>
    <td valign="top" width="60px">
        <?php if ($lastHandle !== $comment->user_handle) {
            echo MOD_layoutbits::PIC_50_50($comment->user_handle,'',$style='framed');
        }
        ?>
    </td>
    <td valign="top">
        <h<?=$headingLevel?>><?=htmlentities($comment->title, ENT_COMPAT, 'utf-8')?></h<?=$headingLevel?>>
        <div class="author small">
            <?php echo $words->getFormatted('written_by'); ?> <a href="user/<?=$comment->user_handle?>"><?=$comment->user_handle?></a>
            <a href="blog/<?=$blog->user_handle?>" title="Read blog by <?=$blog->user_handle?>"><img src="images/icons/blog.gif" alt="" /></a>
             - <?php echo MOD_layoutbits::ago($comment->unix_created)?> <a href="#" title="<?=date($format['short'], $comment->unix_created)?>">(i)</a>
        </div>
        <div class="text"><?=nl2br(htmlentities($comment->text, ENT_COMPAT, 'utf-8'))?></div>
    </td>
    </tr>
    </table>
    </div>
<?php
}
?>
