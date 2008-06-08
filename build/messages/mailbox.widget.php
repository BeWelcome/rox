<?php


class MailboxWidget extends ItemlistWithPagination
{
    public function render() {
        ?><style>
        tr.odd {background:#f8f8f8;}
        </style><?php
        parent::render(); 
    }
    
    // pagination
    
    protected function hrefPage($i_page) {
        return 'messages/inbox/'.$i_page;
    }
    
    
    //-----------------------------------------------------------------
    // getting the items
    
    protected function getAllItems()
    {
        return $this->getMessages();
    }
    
    
    //-----------------------------------------------------------------
    // table layout
    
    /**
     * Columns for messages table.
     * The $key of a column is used as a suffix for method tableCell_$key
     *
     * @return array table columns, as $name => Column title
     */
    protected function getTableColumns()
    {
        return array(
            'contact' => 'From/To',
            'title' => 'Text',
            'dateSent' => 'Date'
        );
    }
    
    /**
     * Table cell in column 'contact', for the given $message
     *
     * @param unknown_type $message
     */
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
        $date_sent = $message->DateSent;
        $date_created = $message->created;
        $date_string = date("M d, Y - H:i",strtotime($date_created));
        ?>
        <span style="color:silver; font-size:80%"><?=$direction_in ? 'Received on' : 'Sent on' ?></span><br>
        <?=$date_string ?>
        <?php
    }
}


?>