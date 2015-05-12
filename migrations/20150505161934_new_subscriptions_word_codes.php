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
        $this->AddWordCode('ForumEnableAllNotifications', 'Enable all forum and groups notifications', 'Info text in forums/subscriptions');
        $this->AddWordCode('ForumDisableAllNotifications', 'Disable all forum and groups notifications', 'Info text in forums/subscriptions');
        $this->AddWordCode('ForumEnable', 'Enable notifications', 'Button label shown in forums/subscriptions');
        $this->AddWordCode('ForumDisable', 'Disable notifications', 'Button label shown in forums/subscriptions');
        $this->AddWordCode('ForumGroupThreadEnable', 'Enable notifications', 'Button label shown in forums/subscriptions');
        $this->AddWordCode('ForumGroupThreadDisable', 'Disable notifications', 'Button label shown in forums/subscriptions');
        $this->AddWordCode('ForumDisabled', 'Disabled', 'Info text (shown as Button label) in forums/subscriptions if a subscription is disabled');
        $this->AddWordCode('GroupMemberSettingsDisabledInfo', 'Email delivery is currently disabled, just click on \'Update Membership\' to enable it again', 'Info text shown on /groups/&lt;id&gt;/membersettings if mail notifications hae been disabled on /forums/subscriptions');
        $this->AddWordCode('ForumSubscriptions', 'Subscriptions', 'Headline for the subscriptions page /forums/subscriptions');
        $this->AddWordCode('ForumGroupSubscriptions', 'Group Subscriptions', 'Headline for the group subscriptions shown on /forums/subscriptions');
        $this->AddWordCode('ForumThreadSubscriptions', 'Thread Subscriptions', 'Headline for the thread subscriptions shown on /forums/subscriptions');
        $this->AddWordCode('ForumTagSubscriptions', 'Tag Subscriptions', 'Headline for the tag subscriptions shown on /forums/subscriptions');
        $this->AddWordCode('ForumNoGroups', 'You haven\'t joined any group yet.', 'Info text if no groups have been joined on /forum/subscriptions');
        $this->AddWordCode('ForumNoThreadsSubscribed', 'You haven\'t subscribed to any thread yet.', 'Info text if no groups have been joined on /forum/subscriptions');
        $this->AddWordCode('ForumNoTagsSubscribed', 'You haven\'t subscribed to any tag yet.', 'Info text if no groups have been joined on /forum/subscriptions');
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
        $this->RemoveWordCode('ForumGroupThreadEnable');
        $this->RemoveWordCode('ForumGroupThreadDisable');
        $this->RemoveWordCode('ForumDisabled');
        $this->RemoveWordCode('GroupMemberSettingsDisabledInfo');
        $this->RemoveWordCode('ForumSubscriptions');
        $this->RemoveWordCode('ForumGroupSubscriptions');
        $this->RemoveWordCode('ForumThreadSubscriptions');
        $this->RemoveWordCode('ForumTagSubscriptions');
        $this->RemoveWordCode('ForumNoGroups');
        $this->RemoveWordCode('ForumNoThreadsSubscribed');
        $this->RemoveWordCode('ForumNoTagsSubscribed');
    }
}