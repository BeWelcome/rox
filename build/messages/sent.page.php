<?php


class MessagesSentboxPage extends MessagesPageWithMailbox
{
    protected function getSubmenuActiveItem() {
        return 'sent';
    }
    
    protected function mailboxDescription() {

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
        return $this->model->sentMailbox();
    }

    protected function getTableColumns()
    {
        $columns = parent::getTableColumns();
        $columns['from'] = 'To';
        // We don't mark the link of the current sortorder yet, but we could:
        // $sort_current = isset($_GET['sort']) ? $_GET['sort'] : 'date';
        // This would lgo in the a-tag: '.(($sort_current == 'date') ? 'class="sort_selected"' : '').'
        $request_str = implode('/',PRequest::get()->request);
        $dir_str = (isset($_GET['dir']) && $_GET['dir'] != 'ASC') ? 'ASC' : 'DESC';
        $words = new MOD_words();
        $columns['from'] = '<a href="'.$request_str.'?sort=receiver&amp;dir='.$dir_str.'">'.$words->getSilent('To').'</a> / <a href="'.$request_str.'?sort=date&amp;dir='.(isset($_GET['dir']) ? $dir_str : 'ASC').'">'.$words->getSilent('Date').'</a>'.$words->flushBuffer();
        return $columns;
    }
    
    
    protected function hrefPage($i_page) {
        return 'messages/sent/'.$i_page;
    }
}
