<?php

namespace App\Model;

use App\Doctrine\CommentRelationsType;
use App\Entity\Comment;
use App\Entity\Member;
use App\Repository\CommentRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Jfcherng\Diff\LevenshteinDistance;
use Throwable;

class CommentModel
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function getCommentForMemberPair(Member $loggedInMember, Member $member): ?Comment
    {
        // Fetch comment from logged in member to member in route
        $commentRepository = $this->entityManager->getRepository(Comment::class);

        return $commentRepository->findOneBy(['fromMember' => $loggedInMember, 'toMember' => $member]);
    }

    /**
     * @infection-ignore-all
     *
     * Mutations on this function are meaningless (e.g. 999 instead of 1000).
     *
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     *
     *  \todo move check for text difference into function of its own.
     */
    public function checkIfNewExperience(Comment $original, Comment $updated): bool
    {
        $originalRelations = explode(',', $original->getRelations());
        $updatedRelations = explode(',', $updated->getRelations());

        // if a hosting experience was added we can assume that it is a new experience.
        $diff = array_diff($updatedRelations, $originalRelations);

        if (
            \in_array(CommentRelationsType::WAS_GUEST, $diff, true)
            || \in_array(CommentRelationsType::WAS_HOST, $diff, true)
        ) {
            return true;
        }

        $originalText = $original->getComment();
        $updatedText = $updated->getComment();
        if ($originalText === $updatedText) {
            return false;
        }

        $lenOriginalText = \strlen($originalText);
        $lenUpdatedText = \strlen($updatedText);
        // If relations are unchanged check for changes in text of comment
        if (str_starts_with($updatedText, $originalText)) {
            // New text starts with old text and new text is longer
            if ($lenUpdatedText > $lenOriginalText) {
                return true;
            }

            return false;
        }

        $newExperience = false;
        try {
            $maxlen = max(\strlen($updatedText), \strlen($originalText));
            $calculator = new LevenshteinDistance(false, 0, 1000 ** 2);
            $iteration = 0;
            $maxIteration = $maxlen / 1000;
            while ($iteration < $maxIteration && !$newExperience) {
                $currentUpdatedText = substr($updatedText, $iteration * 1000, 1000);
                $currentOriginalText = substr($originalText, $iteration * 1000, 1000);
                $levenshteinDistance = $calculator->calculate(
                    $currentUpdatedText,
                    $currentOriginalText
                )['distance'];

                if ($levenshteinDistance >= max(\strlen($currentUpdatedText), \strlen($currentOriginalText)) / 7) {
                    $newExperience = true;
                }
                ++$iteration;
            }
        } catch (Throwable) {
            // ignore exception and just return false (likely consumed too much memory)
            return $newExperience;
        }

        return $newExperience;
    }

    public function checkCommentSpam(Member $loggedInMember, Comment $comment): bool
    {
        $spamCheckParams = [
            ['duration' => '2 minutes', 'count' => 1],
            ['duration' => '20 minutes', 'count' => 5],
            ['duration' => '6 hours', 'count' => 25],
        ];

        $check1 = $this->checkCommentsDuration($loggedInMember, $comment, $spamCheckParams[0]);
        $check2 = $this->checkCommentsDuration($loggedInMember, $comment, $spamCheckParams[1]);
        $check3 = $this->checkCommentsDuration($loggedInMember, $comment, $spamCheckParams[2]);

        return $check1 || $check2 || $check3;
    }

    public function checkForEmailAddress(Comment $comment): bool
    {
        $commentText = $comment->getComment();
        $count = preg_match_all("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $commentText);

        return $count > 0;
    }

    public function checkForPhoneNumber(Comment $comment): bool
    {
        $commentText = $comment->getComment();
        $found = preg_match("/([0-9][\. \)-]*){8,}/", $commentText);

        return $found > 0;
    }

    /**
     * Whether the member wrote at least `count` comments during the last `duration` that are all (nearly) the same
     * as this one.
     */
    private function checkCommentsDuration(Member $member, Comment $comment, array $params): bool
    {
        /** @var CommentRepository $commentRepository */
        $commentRepository = $this->entityManager->getRepository(Comment::class);
        $comments = $commentRepository->findWrittenBySince($member, new DateTime('-' . $params['duration']));
        if (\count($comments) < $params['count']) {
            return false;
        }

        $texts = array_map(static fn (Comment $written): string => $written->getComment(), $comments);
        $texts[] = $comment->getComment();

        return $this->allSimilar($texts);
    }

    /**
     * Whether every two of the texts are more than 95% alike.
     *
     * @param list<string> $texts
     */
    private function allSimilar(array $texts): bool
    {
        $count = \count($texts);
        for ($i = 0; $i < $count - 1; ++$i) {
            for ($j = $i + 1; $j < $count; ++$j) {
                similar_text($texts[$i], $texts[$j], $percent);
                if ($percent <= 95) {
                    return false;
                }
            }
        }

        return true;
    }
}
