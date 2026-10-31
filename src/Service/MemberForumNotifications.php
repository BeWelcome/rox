<?php

namespace App\Service;

use App\Entity\Member;
use Doctrine\DBAL\Connection;

/**
 * Turns all forum notifications of a member on or off: the threads they are subscribed to and the groups that may
 * send them mails. The subscriptions and memberships stay.
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
            'UPDATE group_membership SET notifications_enabled = ? WHERE member_id = ? AND accept_mass_mail = 1',
            [(int) $enabled, $member->getId()],
        );
    }
}
