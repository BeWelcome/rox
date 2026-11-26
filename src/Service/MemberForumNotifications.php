<?php

namespace App\Service;

use App\Entity\Member;
use Doctrine\DBAL\Connection;

/**
 * Turns all forum notifications of a member on or off: the threads they are subscribed to and every group they are
 * a member of. The subscriptions and memberships stay; turned on again, all of them send mails.
 */
class MemberForumNotifications
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function switch(Member $member, bool $enabled): void
    {
        $this->connection->executeStatement(
            'UPDATE members_threads_subscribed SET notificationsEnabled = ? WHERE IdSubscriber = ?',
            [(int) $enabled, $member->getId()],
        );
        $this->connection->executeStatement(
            'UPDATE group_membership SET notifications_enabled = ? WHERE member_id = ?',
            [(int) $enabled, $member->getId()],
        );
    }
}
