<?php

namespace App\Model;

use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\MemberFlag;
use App\Entity\Message;
use App\Entity\SignupPattern;
use App\Model\Admin\CheckerModel;
use App\Repository\MemberFlagRepository;
use App\Repository\SignupPatternRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;

/**
 * The safety team's answer to spammers who come back with the same name: texts to watch for in the usernames and
 * e-mail addresses of new accounts (SignupPattern). An account that signs up with one is flagged for the team and
 * shadow banned (MemberFlag): the conversations it starts (messages, requests, invitations) look sent to it, but are
 * held back and reach nobody, so it doesn't learn what stops it; with nothing received, nobody answers either. The
 * team releases a held message, or the member with all their held messages, or deals with the account (a ban) on
 * the profile.
 */
class ShadowBanModel
{
    /** how far back a new pattern looks for accounts that signed up with it already */
    public const int RECENT_DAYS = 30;

    /** @var array<int, bool> shadow banned or not, by member id: asked once a request */
    private array $banned = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SignupPatternRepository $patterns,
        private readonly MemberFlagRepository $flags,
        private readonly CheckerModel $checkerModel,
    ) {
    }

    /**
     * What of $username and $email the pattern matches ("username janev12"), or null.
     */
    public static function match(SignupPattern $pattern, string $username, string $email): ?string
    {
        $regex = '/' . str_replace('\*', '.*', preg_quote($pattern->getPattern(), '/')) . '/iu';
        if (SignupPattern::FIELD_EMAIL !== $pattern->getField() && preg_match($regex, $username)) {
            return 'username ' . $username;
        }
        if (SignupPattern::FIELD_USERNAME !== $pattern->getField() && preg_match($regex, $email)) {
            return 'email ' . $email;
        }

        return null;
    }

    /**
     * A new account: flagged and shadow banned when its username or address matches an active pattern.
     */
    public function checkNewMember(Member $member): ?MemberFlag
    {
        foreach ($this->patterns->findActive() as $pattern) {
            $matched = self::match($pattern, (string) $member->getUsername(), (string) $member->getEmail());
            if (null !== $matched) {
                $flag = new MemberFlag($member, $pattern, $matched);
                $this->entityManager->persist($flag);

                return $flag;
            }
        }

        return null;
    }

    public function isShadowBanned(Member $member): bool
    {
        return $this->banned[$member->getId()] ??= $this->flags->isShadowBanned($member);
    }

    /**
     * Holds the message back when its sender is shadow banned: delivered to nobody, no notification, until a checker
     * releases it; the sender sees it as sent. Call before the message is saved.
     */
    public function holdIfShadowBanned(Message $message, Member $sender): Message
    {
        if ($this->isShadowBanned($sender)) {
            $message
                ->addToSpamInfo(SpamInfoType::SHADOW_BAN)
                ->setStatus(MessageStatusType::CHECK)
                ->setFolder(InFolderType::SPAM);
        }

        return $message;
    }

    public function addPattern(string $text, string $field, ?string $note, Member $volunteer): SignupPattern
    {
        $pattern = new SignupPattern()
            ->setPattern($text)
            ->setField($field)
            ->setNote($note)
            ->setCreatedBy($volunteer);
        $this->entityManager->persist($pattern);
        $this->entityManager->flush();

        return $pattern;
    }

    public function setActive(SignupPattern $pattern, bool $active): void
    {
        $pattern->setActive($active);
        $this->entityManager->flush();
    }

    /**
     * The pattern goes; the members it flagged stay flagged (as flagged by a pattern that is gone).
     */
    public function removePattern(SignupPattern $pattern): void
    {
        $this->entityManager->remove($pattern);
        $this->entityManager->flush();
    }

    /**
     * @return list<SignupPattern>
     */
    public function getPatterns(): array
    {
        return $this->patterns->findAllForList();
    }

    /**
     * @return array<int, int> the members each pattern flagged, by pattern id
     */
    public function getFlaggedCounts(): array
    {
        return $this->flags->countByPattern();
    }

    /**
     * The accounts of the last RECENT_DAYS days the pattern matches and that aren't flagged yet: a pattern is usually
     * added after the first ones signed up.
     *
     * @return list<array{member: Member, matched: string}>
     */
    public function getRecentMatches(SignupPattern $pattern): array
    {
        $like = '%' . str_replace('*', '%', addcslashes($pattern->getPattern(), '%_\\')) . '%';
        $queryBuilder = $this->entityManager->getRepository(Member::class)->createQueryBuilder('m')
            ->where('m.created >= :since')
            ->andWhere('m NOT IN (SELECT IDENTITY(f.member) FROM ' . MemberFlag::class . ' f)')
            ->setParameter('since', new DateTime('-' . self::RECENT_DAYS . ' days'))
            ->setParameter('like', $like)
            ->orderBy('m.created', 'DESC')
            ->setMaxResults(200);
        $queryBuilder->andWhere(match ($pattern->getField()) {
            SignupPattern::FIELD_USERNAME => 'LOWER(m.username) LIKE :like',
            SignupPattern::FIELD_EMAIL => 'LOWER(m.email) LIKE :like',
            default => 'LOWER(m.username) LIKE :like OR LOWER(m.email) LIKE :like',
        });

        $matches = [];
        foreach ($queryBuilder->getQuery()->getResult() as $member) {
            $matched = self::match($pattern, (string) $member->getUsername(), (string) $member->getEmail());
            if (null !== $matched) {
                $matches[] = ['member' => $member, 'matched' => $matched];
            }
        }

        return $matches;
    }

    /**
     * Flags (and shadow bans) the members, those not flagged already; returns how many.
     *
     * @param iterable<Member> $members
     */
    public function flagMembers(iterable $members, ?SignupPattern $pattern, string $matchedPrefix = ''): int
    {
        $flagged = 0;
        foreach ($members as $member) {
            if ($this->flags->isFlagged($member)) {
                continue;
            }
            $matched = null !== $pattern ? self::match($pattern, (string) $member->getUsername(), (string) $member->getEmail()) : null;
            $this->entityManager->persist(new MemberFlag($member, $pattern, $matched ?? $matchedPrefix . $member->getUsername()));
            ++$flagged;
        }
        $this->entityManager->flush();
        $this->banned = [];

        return $flagged;
    }

    /**
     * A volunteer flags and shadow bans a member (from the new members tool); false when they are flagged already.
     */
    public function flagByVolunteer(Member $member, Member $volunteer, string $where): bool
    {
        if ($this->flags->isFlagged($member)) {
            return false;
        }
        $this->entityManager->persist(new MemberFlag($member, null, 'flagged by ' . $volunteer->getUsername() . ' in ' . $where));
        $this->entityManager->flush();
        unset($this->banned[$member->getId()]);

        return true;
    }

    /**
     * @param list<int> $memberIds
     *
     * @return array<int, bool> by member id, for the flagged ones: true while shadow banned, false once released
     */
    public function getFlagStates(array $memberIds): array
    {
        return $this->flags->statesFor($memberIds);
    }

    /**
     * @return Pagerfanta<MemberFlag>
     */
    public function getFlags(bool $released, int $page, int $limit): Pagerfanta
    {
        return $this->flags->findFlags($released, $page, $limit);
    }

    public function countOpenFlags(): int
    {
        return $this->flags->countOpen();
    }

    /**
     * The messages of the member held back by the shadow ban and not decided yet, the newest first.
     *
     * @return list<Message>
     */
    public function getHeldMessages(Member $member): array
    {
        return $this->entityManager->getRepository(Message::class)->createQueryBuilder('m')
            ->where('m.sender = :member')
            ->andWhere('m.status = :check')
            ->andWhere('m.spamInfo LIKE :shadow')
            ->setParameter('member', $member)
            ->setParameter('check', MessageStatusType::CHECK)
            ->setParameter('shadow', '%' . SpamInfoType::SHADOW_BAN . '%')
            ->orderBy('m.created', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * The member is no longer shadow banned: their held messages are delivered (with the notifications), what they
     * send from now on goes through.
     */
    public function releaseMember(MemberFlag $flag, Member $volunteer): int
    {
        $held = array_map(static fn (Message $message): int => $message->getId(), $this->getHeldMessages($flag->getMember()));
        $this->releaseMessages($held);
        $flag->release($volunteer);
        $this->entityManager->flush();
        unset($this->banned[$flag->getMember()->getId()]);

        return \count($held);
    }

    /**
     * Delivers held messages (with the notifications); the member stays shadow banned.
     *
     * @param list<int> $messageIds
     */
    public function releaseMessages(array $messageIds): void
    {
        if ([] !== $messageIds) {
            $this->checkerModel->unmarkAsSpamByChecker($messageIds);
        }
    }

    /**
     * Held messages that are spam: they stay with the sender and are counted as spam.
     *
     * @param list<int> $messageIds
     */
    public function rejectMessages(array $messageIds): void
    {
        if ([] !== $messageIds) {
            $this->checkerModel->markAsSpamByChecker($messageIds);
        }
    }
}
