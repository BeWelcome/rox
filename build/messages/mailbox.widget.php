<?php


class MailboxWidget extends ItemlistWithPagination
{
    // pagination
    
    protected function hrefPage($i_page) {
        return 'messages/inbox/'.$i_page;
    }
    
    
    //-----------------------------------------------------------------
    // getting the items
    
    protected function getItemsInRange($begin, $end)
    {
        $items = $this->_getMessages_cached();
        return array_slice($items, $begin, $end - $begin);
    }
    
    protected function itemsTotalBegin() {
        return 0;
    }
    
    protected function itemsTotalCount() {
        return count($this->_getMessages_cached());
    }
    
    
    //-----------------------------------------------------------------
    
    private $_messages_cached = false;
    private function _getMessages_cached()
    {
        if (!$this->_messages_cached) {
            $this->_messages_cached = $this->getMessages();
        }
        return $this->_messages_cached;
    }
    
    
    //-----------------------------------------------------------------
    // table layout
    
    protected function getTableColumns()
    {
        return array(
            'contact' => 'From/To',
            'title' => 'Text',
            'dateSent' => 'Date'
        );
    }
    
    protected function tableCell_contact($message)
    {
        $direction_in = ($message->IdReceiver == $_SESSION['IdMember']);
        $contact_username = $direction_in ? $message->senderUsername : $message->receiverUsername;
        $contact_id = $direction_in ? $message->IdSender : $message->IdReceiver;
        ?>
        <table><tr>
        <td><?=MOD_layoutbits::linkWithPicture($contact_username) ?></td>
        <td>
        <?=$direction_in ? 'From' : 'To' ?><br>
        <a href="bw/member.php?cid=<?=$contact_username ?>"><strong><?=$contact_username ?></strong></a><br>
        <a href="messages/with/<?=$contact_username ?>">mailbox</a><br>
        <a href="messages/compose/<?=$contact_username ?>">send new</a>
        </td>
        </tr></table><?php
    }
    
    protected function tableCell_title($message)
    {
        ?><a href="messages/<?=$message->id ?>"><?=$message->Message ?></a>
        <?php
    }
    
    protected function tableCell_dateSent($message)
    {
        $direction_in = ($message->IdReceiver == $_SESSION['IdMember']);
        ?>
        <span style="color:silver; font-size:80%"><?=$direction_in ? 'Received on' : 'Sent on' ?></span><br>
        <?=$message->DateSent ?>
        <?php
    }
}


?>