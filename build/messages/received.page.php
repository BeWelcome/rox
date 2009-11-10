<?php



class MessagesInboxPage extends MessagesPageWithMailbox
{
    protected function getSubmenuActiveItem() {
        return 'received';
    }
    
    protected function mailboxDescription() {
        //echo 'This is your inbox';
    }
    
    protected function getMailboxWidgetClassname()
    {
        return 'MailboxWidget_Received';
    }
}



class MailboxWidget_Received extends MailboxWidget
{
    protected function getMessages()
    {
        return $this->model->receivedMailbox();
    }
    
    protected function hrefPage($i_page) {
        //return 'messages/received/'.$i_page;
        return 'messages/received/'.$i_page.'?'.$_SERVER['QUERY_STRING'] ;
    }
}
