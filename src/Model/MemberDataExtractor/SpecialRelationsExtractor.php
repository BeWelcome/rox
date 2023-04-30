<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\Relation;
use App\Entity\Member;
use App\Repository\RelationRepository;

final class SpecialRelationsExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extract(Member $member, string $tempDir): string
    {
        $relations = [];
        /** @var RelationRepository $relationsRepository */
        $relationsRepository = $this->getRepository(Relation::class);
        $rawRelations = $relationsRepository->findRelationsFor($member);
        if (!empty($rawRelations)) {
            // build list of relations from raw data (list contains relations from both sides)
            /** @var Relation $relation */
            foreach ($rawRelations as $relation) {
                $author = $relation->getOwner();
                $authorId = $author->getId();
                $recipient = $relation->getReceiver();
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

        return $this->writePersonalDataFile(['relations' => $relations], 'relations', $tempDir . 'relations.html');
    }
}
