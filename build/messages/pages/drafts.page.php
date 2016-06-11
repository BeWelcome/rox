<?php


class MessagesDraftsboxPage extends MessagesPageWithMailbox
{
    protected function getSubmenuActiveItem() {
        return 'drafts';
    }
    
    protected function mailboxDescription() {
        
    }
    
    protected function getMailboxWidgetClassname()
    {
        return 'MailboxWidget_Drafts';
    }
}


class MailboxWidget_Drafts extends MailboxWidget
{
    protected function getMessages()
    {
        $myself_id = $this->_session->get('IdMember');
        return $this->model->filteredMailbox(array(
            'messages.IdSender = '.$myself_id,
            'messages.Status = "Draft"'
        ));
    }
    
    protected function getTableColumns()
    {
        $columns = parent::getTableColumns();
        unset($columns['dateSent']);
        $columns['from'] = 'To';
        return $columns;
    }

    
    protected function hrefPage($i_page) {
        return 'messages/drafts/'.$i_page;
    }
}
