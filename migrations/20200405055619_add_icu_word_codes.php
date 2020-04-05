<?php

use Rox\Tools\RoxMigration;

class AddIcuWordCodes extends RoxMigration
{
    public function up()
    {
        $this->AddWordCode(
            'label.admin.groups.awaiting.approval',
            '{groups, plural, =0 {No groups in queue} one {One group in queue} other {# groups in queue}}',
            'Label for the submenu button for groups awaiting approval'
        );
        $this->AddWordCode(
            'label.admin.comments.reported',
            '{comments, plural, =0 {No reported comment} one {One reported comment} other {# reported comments}}',
            'Label for the submenu button for reported comments'
        );
        $this->AddWordCode(
            'label.admin.messages.reported',
            '{messages, plural, =0 {No reported message} one {One reported message} other {# reported messages}}',
            'Label for the submenu button for groups awaiting approval'
        );
    }

    public function down()
    {
        $this->RemoveWordCode(
            'label.admin.groups.awaiting.approval',
            '{groups, plural, =0 {No groups in queue} one {One group in queue} other {# groups in queue}}',
            'Label for the submenu button for groups awaiting approval'
        );
        $this->RemoveWordCode(
            'label.admin.comments.reported',
            '{comments, plural, =0 {No reported comment} one {One reported comment} other {# reported comments}}',
            'Label for the submenu button for reported comments'
        );
        $this->RemoveWordCode(
            'label.admin.messages.reported',
            '{messages, plural, =0 {No reported message} one {One reported message} other {# reported messages}}',
            'Label for the submenu button for groups awaiting approval'
        );
    }
}
