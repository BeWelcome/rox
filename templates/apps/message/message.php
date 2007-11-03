<?php
/**
 * mesage detailed  page template controller
 *
 * defined vars:
 * $message     - message statement.
 *
 * @package message
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

?>
<h2><?php echo ($is_outbox?$msgText['title_outbox']:$msgText['title_inbox']); ?></h2>
<h3><?php echo htmlentities($message->subject, ENT_COMPAT, 'utf-8'); ?></h3>
<p>
<?php 
if ($is_outbox) {
    // outbox message
    echo $msgText['label_to'].': ';
    $recp = explode(',', $message->recipients);
    foreach ($recp as $userId) {
        $user_handle = $U->getRealHandle($userId);
        echo '<a href="user/'.$user_handle.'">'.$user_handle.'</a> ';
        echo '[<a href="blog/'.$user_handle.'" title="Read blog by '.$user_handle.'"">b</a>] ';
    }
    echo '<br />';
} else {
    // inbox message
    $user_handle = $U->getRealHandle($message->sender_id_foreign);
    echo $msgText['label_from'].': <a href="user/'.$user_handle.'">'.$user_handle.'</a> 
        [<a href="blog/'.$user_handle.'" title="Read blog by '.$user_handle.'"">b</a>]<br />';
}
echo $msgText['label_date'].': '.date($format['short'], $message->unix_created);
?>
</p>
<div>
    <?php echo $message->text; ?>
</div>
<p>
    <form id="message-box" method="post" action="<?php
if ($is_outbox)
    echo 'message/sent/del/';
else
    echo 'message/inbox/del/'
?>" class="def-form">
        <input type="hidden" name="<?=$callbackDeleteId?>" value="1" />
        <input type="hidden" name="del-id[<?php echo $request[2]; ?>]" value="On" />
        <input type="submit" value="<?php echo $msgText['submit_delete']?>"/>
    </form>
</p>
<?php
?>
