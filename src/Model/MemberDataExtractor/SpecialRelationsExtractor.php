<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\FamilyAndFriend;
use App\Entity\Member;
use App\Repository\FamilyAndFriendRepository;

final class SpecialRelationsExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        $relations = [];
        /** @var FamilyAndFriendRepository $relationsRepository */
        $relationsRepository = $this->getRepository(FamilyAndFriend::class);
        $rawRelations = $relationsRepository->findRelationsFor($member);
        if (!empty($rawRelations)) {
            // build list of relations from raw data (list contains relations from both sides)
            /** @var FamilyAndFriend $relation */
            foreach ($rawRelations as $relation) {
                $author = $relation->getOwner();
                $authorId = $author->getId();
                $recipient = $relation->getRelation();
                $recipientId = $recipient->getId();
                if ($recipient !== $member) {
                    $relations[$recipientId] = [];
                    $relations[$recipientId]['right'] = $relation;
                } elseif (\array_key_exists($authorId, $relations)) {
                    $relations[$authorId]['left'] = $relation;
                } else {
                    $relations[$authorId] = [];
                    $relations[$authorId]['left'] = $relation;
                }
            }
        }

        return $this->writePersonalDataFile(['relations' => $relations], 'relations');
    }
}
