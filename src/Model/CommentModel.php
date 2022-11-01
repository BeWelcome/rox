<?php

namespace App\Model;

use App\Doctrine\CommentRelationsType;
use App\Entity\Comment;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use GordonLesti\Levenshtein\Levenshtein;

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

        $levenshtein = new Levenshtein();
        $levenshteinDistance = $levenshtein->levenshtein($updatedText, $originalText);

        if ($levenshteinDistance >= max($lenOriginalText, $lenUpdatedText) / 7) {
            return true;
        }

        return false;
    }
}
