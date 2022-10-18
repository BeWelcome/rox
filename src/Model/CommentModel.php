<?php

namespace App\Model;

use App\Entity\Comment;
use App\Entity\Member;
use App\Repository\CommentRepository;
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
        $diff = array_diff($updatedRelations, $originalRelations);
        $intersect = array_intersect($updatedRelations, $originalRelations);

        // if no updated relations are in original relations it's not a new relation but an existing one was corrected
        // But the text changes might still point to a new experience
        if (0 == count($intersect)) {
            return false;
        } else {
            if (count($intersect) == count($originalRelations) && 0 != count($diff)) {
                return true;
            }
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
