<?php

class MessagesInboxPage extends MessagesPageWithMailbox
{
    protected function getSubmenuActiveItem()
    {
        return 'received';
    }

    protected function mailboxDescription()
    {
        //echo 'This is your inbox';
    }

    protected function getMailboxWidgetClassname()
    {
        return 'MailboxWidget_Received';
    }
}