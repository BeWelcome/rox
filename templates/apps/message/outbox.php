<?php
/**
 * mesage outbox page template controller
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

<script type="text/javascript">//<!--
function checkall(id) {
    var obj = document.getElementById(id);
    var len = obj.elements.length;
    var check = document.getElementById('message-checkall').checked;
    for (var i=0; i < len; ++i) {
        obj.elements[i].checked = check;
    }
}
//-->
</script>

<h2><?php echo $outboxText['title_outbox'];?></h2>
<?php
if (isset($request[3]) && $request[3] == 'finish') {
    echo '<p class="notify">'.$outboxText['message_deleted'].'</p>';
}
?>

<div class="messages">
<?php
if (!$messages)
    echo $outboxText['no_messages'];
else
{
?>
<form id="message-box" method="post" action="message/sent/del" class="def-form">
    <table  cellpadding="0" cellspacing="0">
        <tr>
            <th class="checkall"><input type="checkbox" onClick="checkall('message-box')" id="message-checkall" /></th>
            <th><?php echo $outboxText['label_subject']; ?></th>
            <th><?php echo $outboxText['label_when']; ?></th>
            <th><?php echo $outboxText['label_to']; ?></th>
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
            <td class="subject">
                <a href="message/sent/<?php echo $msg->message_id; ?>"><?php echo htmlentities($msg->subject, ENT_COMPAT, 'utf-8'); ?></a>
            </td>
            <td class="date"><?php echo date($format['short'], $msg->unix_created); ?></td>
            <td class="recipient"><?php 
$recp_count = count(explode(',', $msg->recipients));
if ($recp_count == 1) {
    // single receiver.
    $U = new User();
    $user_handle = $U->getRealHandle($msg->recipients);
    echo '<a href="user/'.$user_handle.'">'.$user_handle.'</a>';
} else {
    echo $outboxText['several_recipients'].' ('.$recp_count.')';
}
?></td>
        </tr>
<?php
    }
?>
    </table>
    <p>
        <input type="hidden" name="<?=$callbackId?>" value="1"/>
        <input type="submit" value="<?php echo $outboxText['submit_delete_checked']?>"/>
    </p>
</form>
<?php 
}
?>
</div>
