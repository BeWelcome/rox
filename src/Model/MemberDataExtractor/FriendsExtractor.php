<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Friend;
use App\Entity\Member;
use App\Repository\FriendRepository;

final class FriendsExtractor extends AbstractExtractor implements ExtractorInterface
{
    public function extract(Member $member, string $tempDir): string
    {
        $friends = [];
        /** @var FriendRepository $friendRepository */
        $friendRepository = $this->getRepository(Friend::class);
        $rawFriends = $friendRepository->findFriendsFor($member);
        if (!empty($rawFriends)) {
            // build list of friends from raw data (list contains friends from both sides)
            /** @var Friend $friend */
            foreach ($rawFriends as $friend) {
                /* \todo fix friend extraction
                $author = $friend->getOwner();
                $authorId = $author->getId();
                $recipient = $friend->getReceiver();
                $recipientId = $recipient->getId();
                if ($recipient !== $member) {
                    $friends[$recipientId] = [];
                    $friends[$recipientId]['right'] = $friend;
                } elseif (\array_key_exists($authorId, $friends)) {
                    $friends[$authorId]['left'] = $friend;
                } else {
                    $friends[$authorId] = [];
                    $friends[$authorId]['left'] = $friend;
                }
                */
            }
        }

        return $this->writePersonalDataFile(['friends' => $friends], 'friends', $tempDir . 'friends.html');
    }
}
