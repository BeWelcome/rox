<?php

namespace Rox\Message\Service;

use Illuminate\Database\Eloquent\Builder;
use Rox\Member\Model\Member;
use Rox\Message\Model\Message;

interface MessageServiceInterface
{
    /**
     * @param Member $member
     * @param $filter
     * @param $sort
     * @param $sortDir
     *
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function getFilteredMessages(Member $member, $filter, $sort, $sortDir);

    /**
     * @param Message $message
     * @param Member  $deletingMember
     */
    public function deleteMessage(Message $message, Member $deletingMember);

    /**
     * @param Message $message
     * @param $destinationFolder
     */
    public function moveMessage(Message $message, $destinationFolder);

    /**
     * @param Message $message
     * @param $state
     *
     * @void
     */
    public function markMessage(Message $message, $state);
}
