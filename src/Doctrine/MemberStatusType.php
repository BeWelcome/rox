<?php

namespace App\Doctrine;

class MemberStatusType extends EnumType
{
    // Possible member statuses
    public const string AWAITING_MAIL_CONFIRMATION = 'MailToConfirm';
    public const string MAIL_CONFIRMED = 'MailConfirmed';
    public const string PENDING = 'Pending';
    public const string DUPLICATE_SIGNED = 'DuplicateSigned';
    public const string NEED_MORE = 'NeedMore';
    public const string REJECTED = 'Rejected';
    public const string COMPLETED_PENDING = 'CompletedPending';
    public const string ACTIVE = 'Active';
    public const string ACCOUNT_ACTIVATED = 'Activated';
    public const string TAKEN_OUT = 'TakenOut';
    public const string BANNED = 'Banned';
    public const string SLEEPER = 'Sleeper';
    public const string CHOICE_INACTIVE = 'ChoiceInactive';
    public const string OUT_OF_REMIND = 'OutOfRemind';
    public const string RENAMED = 'Renamed';
    public const string ACTIVE_HIDDEN = 'ActiveHidden';
    public const string SUSPENDED = 'SuspendedBeta';
    public const string ASKED_TO_LEAVE = 'AskToLeave';
    public const string STOP_BORING_ME = 'StopBoringMe';
    public const string PASSED_AWAY = 'PassedAway';
    public const string BUGGY = 'Buggy';

    public const string ACTIVE_ALL = "'" .
        self::ACTIVE . "', '" .
        self::ACTIVE_HIDDEN . "', '" .
        self::CHOICE_INACTIVE . "', '" .
        self::OUT_OF_REMIND . "', '" .
        self::PENDING . "'";

    public const array ACTIVE_ALL_ARRAY = [
        self::ACTIVE_HIDDEN,
        self::ACTIVE,
        self::CHOICE_INACTIVE,
        self::OUT_OF_REMIND,
        self::PENDING,
    ];

    public const string ACTIVE_SEARCH = "'" .
        self::ACTIVE . "', '" .
        self::ACTIVE_HIDDEN . "', '" .
        self::OUT_OF_REMIND . "', '" .
        self::PENDING . "'";

    public const string ACTIVE_WITH_MESSAGES = "'" .
        self::ACTIVE . "', '" .
        self::OUT_OF_REMIND . "', '" .
        self::PENDING . "'";

    public const string MEMBER_COMMENTS = "'" .
        self::ACTIVE . "', '" .
        self::ACTIVE_HIDDEN . "', '" .
        self::ASKED_TO_LEAVE . "', '" .
        self::CHOICE_INACTIVE . "', '" .
        self::OUT_OF_REMIND . "', '" .
        self::SUSPENDED . "', '" .
        self::PENDING . "'";

    public const array MEMBER_COMMENTS_ARRAY = [
        self::ACTIVE,
        self::ACTIVE_HIDDEN,
        self::ASKED_TO_LEAVE,
        self::CHOICE_INACTIVE,
        self::OUT_OF_REMIND,
        self::SUSPENDED,
        self::PENDING,
    ];

    public const array MEMBER_PROFILE_LINKED = [
        self::ACTIVE,
        self::ACTIVE_HIDDEN,
        self::OUT_OF_REMIND,
        self::PENDING,
    ];

    public const array STATUSES_IN_USE = [
        'Active',
        'MailToConfirm',
        'PassedAway',
        'Pending',
        'Banned',
        'ChoiceInactive',
        'OutOfRemind',
        'ActiveHidden',
        'SuspendedBeta',
        'AskToLeave',
    ];

    protected string $name = 'member_status';

    protected array $values = [
        self::AWAITING_MAIL_CONFIRMATION,
        self::PENDING,
        self::DUPLICATE_SIGNED,
        self::NEED_MORE,
        self::REJECTED,
        self::COMPLETED_PENDING,
        self::ACTIVE,
        self::TAKEN_OUT,
        self::BANNED,
        self::SLEEPER,
        self::CHOICE_INACTIVE,
        self::OUT_OF_REMIND,
        self::RENAMED,
        self::ACTIVE_HIDDEN,
        self::SUSPENDED,
        self::ASKED_TO_LEAVE,
        self::STOP_BORING_ME,
        self::PASSED_AWAY,
        self::BUGGY,
        self::ACCOUNT_ACTIVATED,
        self::MAIL_CONFIRMED,
    ];

    public function getStatuses(): array
    {
        $translationIds = self::STATUSES_IN_USE;
        array_walk($translationIds, function (&$item) {
            $item = strtolower('MemberStatus' . $item);
        });

        return array_combine($translationIds, self::STATUSES_IN_USE);
    }
}
