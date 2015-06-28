<?php

use Phinx\Migration\AbstractMigration;

class MailbotLinkWordCodes extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->AddWordCode('MailbotDisableGroup','Disable group notifications (this group only, temporarily)', 'Used in the notification to point the member how to disable the notifications for a group');
        $this->AddWordCode('MailbotUnsubscribeGroup','Unsubscribe group notification (this group only)', 'Used in the notification to point the member how to unsubscribe from the group');
        $this->AddWordCode('MailbotUnsubscribeThread','Unsubscribe from this thread', 'Used in the notification to allow member to quickly unsubscribe from a thread');
        $this->AddWordCode('MailbotDisableThread','Temporarily disable notifications for this thread', 'Used in the notification to point the member how to disable the notification');
        $this->AddWordCode('MailbotUnsubscribeTag','Unsubscribe from this tag', 'Used in the notification to allow member to quickly unsubscribe from a tag');
        $this->AddWordCode('MailbotDisableTag','Temporarily disable notifications for this tag', 'Used in the notification to point the member how to disable the notification');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->RemoveWordCode('MailbotDisableThread');
        $this->RemoveWordCode('MailbotDisableGroup');
        $this->RemoveWordCode('MailbotUnsubscribeGroup');
        $this->RemoveWordCode('MailbotUnsubscribeThread');
        $this->RemoveWordCode('MailbotUnsubscribeTag');
        $this->RemoveWordCode('MailbotDisableTag');
    }
}