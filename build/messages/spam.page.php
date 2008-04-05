<?php




class MessagesSpamboxPage extends MessagesPageWithMailbox
{
    protected function getSubmenuActiveItem() {
        return 'spam';
    }
    
    protected function mailboxDescription() {
        echo 'These messages are marked as spam';
    }
    
    protected function getMailboxWidgetClassname()
    {
        return 'MailboxWidget_Spam';
    }
}


class MailboxWidget_Spam extends MailboxWidget
{
    protected function getMessages()
    {
        return $this->model->filteredMailbox(array(
            'IdReceiver = '.$_SESSION['IdMember'],
            'SpamInfo = "SpamSayMember"'
        ));
    }

    protected function getTableColumns()
    {
        $columns = parent::getTableColumns();
        $columns['contact'] = 'From';
        return $columns;
    }
    
    protected function hrefPage($i_page) {
        return 'messages/spam/'.$i_page;
    }
    
}



?>