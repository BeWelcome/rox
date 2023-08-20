<?php

namespace App\Model;

use App\Doctrine\CommentRelationsType;
use App\Entity\Comment;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Jfcherng\Diff\LevenshteinDistance;
use Throwable;

class CommentModel
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getCommentForMemberPair(Member $loggedInMember, Member $member): ?Comment
    {
        // Fetch comment from logged in member to member in route
        $commentRepository = $this->entityManager->getRepository(Comment::class);

        return $commentRepository->findOneBy(['fromMember' => $loggedInMember, 'toMember' => $member]);
    }

    public function checkIfNewExperience(Comment $original, Comment $updated): bool
    {
        $originalRelations = explode(',', $original->getRelations());
        $updatedRelations = explode(',', $updated->getRelations());

        // if a hosting experience was added we can assume that it is a new experience.
        $diff = array_diff($updatedRelations, $originalRelations);

        if (
            in_array(CommentRelationsType::WAS_GUEST, $diff)
            || in_array(CommentRelationsType::WAS_HOST, $diff)
        ) {
            return true;
        }

        $originalText = $original->getTextFree();
        $updatedText = $updated->getTextFree();
        if ($originalText === $updatedText) {
            return false;
        }

        $lenOriginalText = strlen($originalText);
        $lenUpdatedText = strlen($updatedText);
        // If relations are unchanged check for changes in text of comment
        if (0 === strpos($updatedText, $originalText)) {
            // New text starts with old text and new text is longer
            if ($lenUpdatedText > $lenOriginalText) {
                return true;
            }
        }

        $newExperience = false;
        try {
            $maxlen = max(strlen($updatedText), strlen($originalText));
            $calculator = new LevenshteinDistance(false, 0, 1000**2);
            $iteration = 0;
            $maxIteration = $maxlen / 1000;
            while ($iteration < $maxIteration && !$newExperience) {

                $currentUpdatedText = substr($updatedText, $iteration * 1000, 1000);
                $currentOriginalText = substr($originalText, $iteration * 1000, 1000);
                $levenshteinDistance = ($calculator->calculate(
                    $currentUpdatedText,
                    $currentOriginalText)
                )['distance'];

                if ($levenshteinDistance >= max(strlen($currentUpdatedText), strlen($currentOriginalText)) / 7) {
                    $newExperience = true;
                }
                $iteration++;
            }
        } catch(Throwable $e) {
            // ignore exception and just return false (likely consumed too much memory)
            return $newExperience;
        }

        return $newExperience;
    }
}
