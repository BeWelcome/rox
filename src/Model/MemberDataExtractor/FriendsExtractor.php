<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Friend;
use App\Entity\Member;

/**
 * The member's friendships, confirmed or pending, with who asked and when.
 */
final class FriendsExtractor extends AbstractExtractor implements ExtractorInterface
{
    public function extract(Member $member, string $tempDir): string
    {
        $repository = $this->getRepository(Friend::class);
        /** @var Friend[] $friendships */
        $friendships = array_merge(
            $repository->findBy(['left' => $member]),
            $repository->findBy(['right' => $member]),
        );

        $relations = array_map(static fn (Friend $friend): array => [
            'other' => $friend->getOther($member),
            'requester' => $friend->getRequester(),
            'created' => $friend->getCreated(),
            'confirmed' => $friend->getConfirmed(),
        ], $friendships);

        return $this->writePersonalDataFile(['relations' => $relations], 'relations', $tempDir . 'relations.html');
    }
}
