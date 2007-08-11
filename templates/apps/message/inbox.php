<?php
/**
 * mesage inbox page template controller
 *
 * defined vars:
 * $messages     - messages statement.
 *
 * @package message
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

?>

<h2><?php echo $inboxText['title_inbox'];?></h2>
<?php
if (isset($request[3]) && $request[3] == 'finish') {
    echo '<p class="notify">'.$inboxText['message_deleted'].'</p>';
}
?>
<div class="messages">
<?php
if (!$messages)
    echo $inboxText['no_messages'];
else
{
?>
<form id="message-box" method="post" action="message/inbox/del" class="def-form">
    <table  cellpadding="0" cellspacing="0">
        <tr>
            <th class="checkall"><input type="checkbox" onClick="checkall('message-box')" id="message-checkall" /></th>
            <th><?php echo $inboxText['label_subject']; ?></th>
            <th><?php echo $inboxText['label_when']; ?></th>
            <th><?php echo $inboxText['label_from']; ?></th>
        </tr>            
<?php
    $alt = false;
    foreach ($messages as $msg) {
        $alt = !$alt;
?>
        <tr class="<?php echo ($alt?'alt':'');?>">
            <td>
                <input type="checkbox" name="del-id[<?php echo $msg->message_id;?>]" />
            </td>
            <td<?php 
    if (!$msg->seen) {
        echo ' class="new subject"><span class="new">'.$inboxText['label_new'].'</span> ';
    } else {
        echo '>';
    }
    echo '<a href="message/inbox/'.$msg->message_id.'">'.htmlentities($msg->subject, ENT_COMPAT, 'utf-8').'</a>';
          ?></td>
            <td class="date"><?php echo date($format['short'], $msg->unix_created); ?></td>
            <td class="sender"><?php echo '<a href="user/'.$msg->user_handle.'">'.$msg->user_handle.'</a>'; ?></td>
        </tr>
<?php
    }
?>
    </table>
    <p>
        <input type="hidden" name="<?=$callbackId?>" value="1"/>
        <input type="submit" value="<?php echo $inboxText['submit_delete_checked']?>"/>
    </p>
        
</form>
<?php 
}
?>
</div>
