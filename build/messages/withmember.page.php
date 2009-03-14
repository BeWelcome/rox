<?php




class MessagesContactboxPage extends MessagesPageWithMailbox
{
    protected function mailboxDescription()
    {
        $contactUsername = $this->contact_member->Username;
        $myselfUsername = $_SESSION['Username'];
        ?>
        <div class="floatbox">
            <div class="float_left framed">
                <?=MOD_layoutbits::PIC_50_50($contactUsername,'')?>
            </div>
            <h4>All Messages with <a href="people/<?=$contactUsername ?>"><?=$contactUsername ?></a>
            </h4>
            (in both directions)
        </div>
        <?php
    }
    
    protected function getMailboxWidgetClassname()
    {
        return 'MailboxWidget_WithMember';
    }
    
    protected function getMailboxWidget()
    {
        $widget = parent::getMailboxWidget();
        $widget->contact_member = $this->contact_member;
        return $widget;
    }
}


class MailboxWidget_WithMember extends MailboxWidget
{
    protected function getMessages()
    {
        return $this->model->getMessagesWith($this->contact_member->id);
    }
    
    protected function getTableColumns()
    {
        return array(
            'status' => 'Status',
            'from' => 'From/To',
            'message' => 'Text'
        );
    }    
    protected function tableCell_status($message)
    {
        $direction_in = ($message->IdReceiver == $_SESSION['IdMember']);
        $contact_username = $direction_in ? $message->senderUsername : $message->receiverUsername;
        $contact_id = $direction_in ? $message->IdSender : $message->IdReceiver;
        ?>
        <p style="text-align: center"><img src="images/icons/dir_read_<?=$direction_in ? 'left' : 'right' ?>.png" alt="<?=$direction_in ? 'From' : 'To' ?>" title="<?=$direction_in ? 'From' : 'To' ?>"><br />
        <span style="color:silver; font-size:80%"><?=$direction_in ? 'Received' : 'Sent' ?></span><p>
        <?php
    }
    
    protected function tableCell_from($message)
    {
        $direction_in = ($message->IdReceiver == $_SESSION['IdMember']);
        $contact_username = $direction_in ? $message->senderUsername : $message->receiverUsername;
        $link_username = $message->senderUsername;
        $date_sent = $message->DateSent;
        $date_created = $message->created;
        $layoutbits = new MOD_layoutbits();
        $date_string = date("M d, Y - H:i",strtotime($date_created));
        ?>
        <table><tr>
        <td>
        <?=MOD_layoutbits::PIC_30_30($message->senderUsername,'')?>
        </td>
        <td>
        <a style="color: #333;" href="people/<?=$contact_username ?>"><strong><?=$link_username ?></strong></a>
        <br />
        <span class="small"><?=$layoutbits->ago(strtotime($date_created)) ?></span>
        </td>
        </tr></table>
        <?php
    }
    
    protected function hrefPage($i_page) {
        return 'messages/with/'.$this->contact_member->Username.'/'.$i_page;
    }
}





?>
