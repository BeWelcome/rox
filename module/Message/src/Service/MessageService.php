<?php

namespace Rox\Message\Service;

use Rox\Member\Model\Member;
use Rox\Message\Model\Message;

class MessageService implements MessageServiceInterface
{
    public function getFilteredMessages(Member $member, $filter, $sort, $sortDir)
    {
        $message = new Message();

        $q = $message->newQuery();

        if ($filter === 'sent') {
            $q->where('IdSender', $member->id);
        } else {
            $q->where('IdReceiver', $member->id);
        }

        if ($filter === 'spam') {
            $q->where('InFolder', 'Spam');
        } else {
            $q->where('InFolder', 'Normal');
            $q->where('messages.Status', 'Sent');
        }

        $q->where('DeleteRequest', 'NOT LIKE', '%receiverdeleted%');

        if ($sort === 'date') {
            $q->orderByRaw('IF(messages.created > messages.DateSent, messages.created, messages.DateSent) ' . $sortDir);
        } elseif ($sort === 'sender') {
            $q->join('members', 'messages.IdSender', '=', 'members.id');

            $q->orderBy('members.Username', $sortDir);
        } else {
            throw new \InvalidArgumentException();
        }

        return $q;
    }

    /**
     * Mark a message as deleted for a particular member. Member must be either
     * the sender or receiver.
     *
     * Refactored from \MessagesModel::deleteMessage
     *
     * @param Message $message
     * @param Member $deletingMember
     */
    public function deleteMessage(Message $message, Member $deletingMember)
    {
        $deleteRequest = $message->DeleteRequest;

        if ($message->sender->id === $deletingMember->id) {
            if ($deleteRequest === 'receiverdeleted') {
                $deleteRequest = 'senderdeleted,receiverdeleted';
            } else {
                $deleteRequest = 'senderdeleted';
            }
        }

        if ($message->receiver->id === $deletingMember->id) {
            if ($deleteRequest === 'senderdeleted') {
                $deleteRequest = 'senderdeleted,receiverdeleted';
            } else {
                $deleteRequest = 'receiverdeleted';
            }
        }

        if ($deleteRequest === $message->DeleteRequest) {
            throw new \InvalidArgumentException('No change determined for deleted state.');
        }

        if ($message->DeleteRequest !== '') {
            // TODO Isn't this duplicating?
            $deleteRequest .= ',' . $message->DeleteRequest;
        }

        $message->DeleteRequest = $deleteRequest;

        $message->save();
    }

    public function moveMessage(Message $message, $destinationFolder)
    {
        $folders = [Message::FOLDER_INBOX, Message::FOLDER_SPAM];

        if (!is_string($destinationFolder) || !in_array($destinationFolder, $folders)) {
            throw new \InvalidArgumentException('$destinationFolder is invalid.');
        }

        $message->InFolder = $destinationFolder;

        $message->save();
    }

    public function markMessage(Message $message, $state)
    {
        if ($state === Message::STATE_READ) {
            $message->WhenFirstRead = $message->freshTimestamp();
        } elseif ($state === Message::STATE_UNREAD) {
            $message->WhenFirstRead = '0000-00-00 00:00:00';
        } else {
            throw new \InvalidArgumentException('$state is invalid.');
        }

        $message->save();
    }
}
