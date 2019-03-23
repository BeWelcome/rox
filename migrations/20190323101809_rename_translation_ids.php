<?php


use Rox\Tools\RoxMigration;

class RenameTranslationIds extends RoxMigration
{
    public function up()
    {
        $this->RenameWordCode("MyMessagesReceived", "messages.received");
        $this->RenameWordCode("GroupsJoinPublic", "label.group.join.public");
        $this->RenameWordCode("GroupsJoinApproved", "label.group.jain.approve");
        $this->RenameWordCode("GroupsVisiblePosts", "label.group.posts.visible");
        $this->RenameWordCode("GroupsInvisiblePosts", "label.group.posts.invisible");
        $this->RenameWordCode("GroupsJoinHeading", "headline.group.join");
        $this->RenameWordCode("GroupsVisiblePostsHeading", "headline.group.posts");
        $this->RenameWordCode("GroupsAddImage", "headline.group.picture");
    }

    public function down()
    {
        $this->RevertRenameWordCode("MyMessagesReceived", "messages.received");
        $this->RevertRenameWordCode("GroupsJoinPublic", "label.group.join.public");
        $this->RevertRenameWordCode("GroupsJoinApproved", "label.group.jain.approve");
        $this->RevertRenameWordCode("GroupsVisiblePosts", "label.group.posts.visible");
        $this->RevertRenameWordCode("GroupsInvisiblePosts", "label.group.posts.invisible");
        $this->RevertRenameWordCode("GroupsJoinHeading", "headline.group.join");
        $this->RevertRenameWordCode("GroupsVisiblePostsHeading", "headline.group.posts");
        $this->RevertRenameWordCode("GroupsAddImage", "headline.group.picture");
    }
}
