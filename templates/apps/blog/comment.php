<?
/**
 * defined vars:
 * $comment     - the comment object to display.
 * $count       - index of this comment.
 */
if (!isset($headingLevel)) {
	$headingLevel = 4;
}
?>
<div class="comment" id="c<?=$comment->comment_id?>">
    <div class="author"><a href="<?=implode('/', $request).'#c'.$comment->comment_id?>">#<?=$count+1?></a> 
        <?=$commentsText['written_by']?> <a href="user/<?=$comment->user_handle?>"><?=$comment->user_handle?></a>
        [<a href="blog/<?=$comment->user_handle?>" title="Read blog by <?=$comment->user_handle?>">b</a>]
        - <?=date($format['short'], $comment->unix_created)?>
    </div>
    <h<?=$headingLevel?>><?=htmlentities($comment->title, ENT_COMPAT, 'utf-8')?></h<?=$headingLevel?>>
    <div class="text"><?=nl2br(htmlentities($comment->text, ENT_COMPAT, 'utf-8'))?></div>
</div>
