<?php
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
