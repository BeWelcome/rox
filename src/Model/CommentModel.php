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
            $calculator = new LevenshteinDistance(false, 0, 1000 ** 2);
            $iteration = 0;
            $maxIteration = $maxlen / 1000;
            while ($iteration < $maxIteration && !$newExperience) {
                $currentUpdatedText = substr($updatedText, $iteration * 1000, 1000);
                $currentOriginalText = substr($originalText, $iteration * 1000, 1000);
                $levenshteinDistance = ($calculator->calculate(
                    $currentUpdatedText,
                    $currentOriginalText
                )
                )['distance'];

                if ($levenshteinDistance >= max(strlen($currentUpdatedText), strlen($currentOriginalText)) / 7) {
                    $newExperience = true;
                }
                $iteration++;
            }
        } catch (Throwable $e) {
            // ignore exception and just return false (likely consumed too much memory)
            return $newExperience;
        }

        return $newExperience;
    }

    public function checkCommentSpam(Member $loggedInMember, Comment $comment): bool
    {
        $spamCheckParams = [
            ['duration' => '00:02:00', 'count' => 1],
            ['duration' => '00:20:00', 'count' => 5],
            ['duration' => '06:00:00', 'count' => 25],
        ];

        $check1 = $this->checkCommentsDuration($loggedInMember, $comment, $spamCheckParams[0]);
        $check2 = $this->checkCommentsDuration($loggedInMember, $comment, $spamCheckParams[1]);
        $check3 = $this->checkCommentsDuration($loggedInMember, $comment, $spamCheckParams[2]);

        return $check1 || $check2 || $check3;
    }

    private function checkCommentsDuration(Member $member, Comment $comment, array $params): bool
    {
        $duration = $params['duration'];
        $count = $params['count'];

        $result = false;
        $commentCount = $this->entityManager
            ->getConnection()
            ->executeQuery(
                "
                    SELECT
                        COUNT(*) as cnt
                    FROM
                        comments c
                    WHERE
                        c.IdFromMember = :memberId
                        AND TIMEDIFF(NOW(), created) < :duration
                ",
                [ ':memberId' => $member->getId(), ':duration' => $duration]
            )
            ->fetchOne()
        ;

        if ($commentCount >= $count) {
            // Okay limit was hit, check for comment quality
            // Get all comments written during the given duration
            $comments = $this->entityManager
                ->getConnection()
                ->executeQuery(
                    "
                        SELECT
                            c.TextFree
                        FROM
                            comments c
                        WHERE
                            c.IdFromMember = :memberId
                            AND TIMEDIFF(NOW(), created) < :duration
                    ",
                    [ ':memberId' => $member->getId(), ':duration' => $duration]
                )
                ->fetchAllAssociative()
            ;
            $result = $this->checkCommentSimilarity($comments, $comment);
        }

        return $result;
    }

    private function checkCommentSimilarity(array $comments, Comment $comment): bool
    {
        $similar = 0;
        $comments[count($comments)] = ['TextFree' => $comment->getTextfree()];
        $count = count($comments);
        for ($i = 0; $i < $count - 1; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                similar_text(
                    $comments[$i]['TextFree'],
                    $comments[$j]['TextFree'],
                    $percent
                );
                if ($percent > 95) {
                    $similar++;
                }
            }
        }
        return $similar != $count * ($count - 1);
    }

    public function checkForEmailAddress(Comment $comment): bool
    {
        $commentText = $comment->getTextfree();
        $count = preg_match_all("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $commentText, $matches);

        return $count > 0;
    }

    public function checkForPhoneNumber(Comment $comment): bool
    {
        $commentText = $comment->getTextfree();
        $found = preg_match("/([0-9][\. \)-]*){8,}/", $commentText);

        return $found > 0;
    }
}
