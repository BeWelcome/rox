<?php

use Phinx\Migration\AbstractMigration;

class NewSubscriptionsWordCodes  extends Rox\Tools\RoxMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        // Add word codes as needed
        $this->AddWordCode('ForumEnableAllNotifications', 'Enable all forum and gropus notifications', 'Info text in forums/subscriptions');
        $this->AddWordCode('ForumDisableAllNotifications', 'Disable all forum and gropus notifications', 'Info text in forums/subscriptions');
        $this->AddWordCode('ForumEnable', 'Enable', 'Button label shown in forums/subscriptions');
        $this->AddWordCode('ForumDisable', 'Disable', 'Button label shown in forums/subscriptions');
        $this->AddWordCode('ForumDisabled', 'Disabled', 'Info text (shown as Button label) in forums/subscriptions if a subscription is disabled');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Remove word codes
        $this->RemoveWordCode('ForumEnableAllNotifications');
        $this->RemoveWordCode('ForumDisableAllNotifications');
        $this->RemoveWordCode('ForumEnable');
        $this->RemoveWordCode('ForumDisable');
        $this->RemoveWordCode('ForumDisabled');
    }
}