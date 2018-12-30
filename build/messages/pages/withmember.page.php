<?php




class MessagesContactboxPage extends MessagesPageWithMailbox
{
    protected function mailboxDescription()
    {
        $words = new MOD_words();
        $contactUsername = $this->contact_member->Username;
        $myselfUsername = $this->_session->get('Username');
        ?>
            <div class="col-4 col-md-1">
                <?=MOD_layoutbits::PIC_50_50($contactUsername,'')?>
            </div>
            <div class="col-8 col-md-11">
                <h4><?php echo $words->get('MessagesAllWithmember', '<a href="members/' . $contactUsername . '">' . $contactUsername . '</a>'); ?></h4>
                (<?php echo $words->get('MessagesAllBothDirections'); ?>)
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
        $request_str = implode('/',PRequest::get()->request);
        $dir_str = (isset($_GET['dir']) && $_GET['dir'] != 'ASC') ? 'ASC' : 'DESC';
        $words = new MOD_words();
        return array(
            'select' => '',
            'from' => '<a href="'.$request_str.'?sort=sender&amp;dir='.$dir_str.'">'.$words->getSilent('From').'</a> / <a href="'.$request_str.'?sort=date&amp;dir='.(isset($_GET['dir']) ? $dir_str : 'ASC').'">'.$words->getSilent('Date').'</a>'.$words->flushBuffer(),
            'message' => $words->get('MessagesText'),
            // 'status' => 'Status'
            //'date' => 'Date',
        );

        return array(
            'select' => '',
            'from' => 'From/To',
            'message' => 'Text'
        );
    }    
    protected function tableCell_status($message)
    {
        $direction_in = ($message->IdReceiver == $this->_session->get('IdMember'));
        // $contact_username = $direction_in ? $message->senderUsername : $message->receiverUsername;
        // $contact_id = $direction_in ? $message->IdSender : $message->IdReceiver;
        ?>
        <p style="text-align: center"><img src="images/icons/dir_read_<?=$direction_in ? 'left' : 'right' ?>.png" alt="<?=$direction_in ? 'From' : 'To' ?>" title="<?=$direction_in ? 'From' : 'To' ?>"><br />
        <span style="color:silver; font-size:80%"><?=$direction_in ? 'Received' : 'Sent' ?></span><p>
        <?php
    }
    
    protected function tableCell_from($message)
    {
        $direction_in = ($message->IdReceiver == $this->_session->get('IdMember'));
        $contact_username = $direction_in ? $message->senderUsername : $message->receiverUsername;
        $link_username = $message->senderUsername;
        // $date_sent = $message->DateSent;
        $date_created = $message->created;
        $layoutbits = new MOD_layoutbits();
        // $date_string = date("M d, Y - H:i",strtotime($date_created));
        ?>

        <div class="media">
            <?=MOD_layoutbits::PIC_50_50($message->senderUsername,'')?>
            <div class="media-body align-self-center">
                <a class="username" href="members/<?=$contact_username ?>"><?=$link_username ?></a>
                <p class="small m-0"><?=$layoutbits->ago(strtotime($date_created)) ?></p>
            </div>
        </div>

        <?php
    }
    
    protected function hrefPage($i_page) {
        return 'messages/with/'.$this->contact_member->Username.'/'.$i_page;
    }
}
