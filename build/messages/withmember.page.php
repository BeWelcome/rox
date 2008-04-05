<?php




class MessagesContactboxPage extends MessagesPageWithMailbox
{
    protected function mailboxDescription()
    {
        $contactUsername = $this->contact_member->Username;
        $myselfUsername = $_SESSION['Username'];
        ?><div class="floatbox">
        <div style="float:left"><?=MOD_layoutbits::linkWithPicture($contactUsername) ?></div>
        <h3>Messages between <a href="bw/member.php?cid=<?=$contactUsername ?>"><?=$contactUsername ?></a>
        and <a href="<?=$myselfUsername ?>"><?=$myselfUsername ?></a> ( = myself)</h3>
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
    
    protected function tableCell_contact($message)
    {
        $direction_in = ($message->IdReceiver == $_SESSION['IdMember']);
        $contact_username = $direction_in ? $message->senderUsername : $message->receiverUsername;
        $contact_id = $direction_in ? $message->IdSender : $message->IdReceiver;
        ?>
        <span style="color:silver; font-size:80%"><?=$direction_in ? 'From' : 'To' ?></span><br>
        <?=$contact_username ?>
        <?php
    }
    
    protected function hrefPage($i_page) {
        return 'messages/with/'.$this->contact_member->Username.'/'.$i_page;
    }
}





?>