<?php


class MessagesSentboxPage extends MessagesPageWithMailbox
{
    protected function getSubmenuActiveItem() {
        return 'sent';
    }
    
    protected function mailboxDescription() {
        echo 'These are your sent messages';
    }
    
    protected function getMailboxWidgetClassname()
    {
        return 'MailboxWidget_Sent';
    }
}


class MailboxWidget_Sent extends MailboxWidget
{
    protected function getMessages()
    {
        return $this->model->filteredMailbox(array(
            'IdSender = '.$_SESSION['IdMember'],
            'messages.Status != "Draft"'
        ));
    }
    

    protected function getTableColumns()
    {
        $columns = parent::getTableColumns();
        $columns['contact'] = 'To';
        return $columns;
    }
    
    protected function hrefPage($i_page) {
        return 'messages/sent/'.$i_page;
    }
}


?>