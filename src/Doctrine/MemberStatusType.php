<?php

namespace App\Doctrine;

class MemberStatusType extends EnumType
{
    // Possible member statuses
    public const AWAITING_MAIL_CONFIRMATION = 'MailToConfirm';
    public const PENDING = 'Pending';
    public const DUPLICATE_SIGNED = 'DuplicateSigned';
    public const NEED_MORE = 'NeedMore';
    public const REJECTED = 'Rejected';
    public const COMPLETED_PENDING = 'CompletedPending';
    public const ACTIVE = 'Active';
    public const TAKEN_OUT = 'TakenOut';
    public const BANNED = 'Banned';
    public const SLEEPER = 'Sleeper';
    public const CHOICE_INACTIVE = 'ChoiceInactive';
    public const OUT_OF_REMIND = 'OutOfRemind';
    public const RENAMED = 'Renamed';
    public const ACTIVE_HIDDEN = 'ActiveHidden';
    public const SUSPENDED = 'SuspendedBeta';
    public const ASKED_TO_LEAVE = 'AskToLeave';
    public const STOP_BORING_ME = 'StopBoringMe';
    public const PASSED_AWAY = 'PassedAway';
    public const BUGGY = 'Buggy';

    public const ACTIVE_ALL = "'" .
        self::ACTIVE . "', '" .
        self::ACTIVE_HIDDEN . "', '" .
        self::CHOICE_INACTIVE . "', '" .
        self::OUT_OF_REMIND . "', '" .
        self::PENDING . "'";

    public const ACTIVE_ALL_ARRAY = [
        self::ACTIVE_HIDDEN,
        self::ACTIVE,
        self::CHOICE_INACTIVE,
        self::OUT_OF_REMIND,
        self::PENDING,
    ];

    public const ACTIVE_SEARCH = "'" .
        self::ACTIVE . "', '" .
        self::ACTIVE_HIDDEN . "', '" .
        self::OUT_OF_REMIND . "', '" .
        self::PENDING . "'";

    public const ACTIVE_WITH_MESSAGES = "'" .
        self::ACTIVE . "', '" .
        self::OUT_OF_REMIND . "', '" .
        self::PENDING . "'";

    public const MEMBER_COMMENTS = "'" .
        self::ACTIVE . "', '" .
        self::ACTIVE_HIDDEN . "', '" .
        self::ASKED_TO_LEAVE . "', '" .
        self::CHOICE_INACTIVE . "', '" .
        self::OUT_OF_REMIND . "', '" .
        self::PENDING . "'";

    /** @var string */
    protected $name = 'member_status';

    /** @var array */
    protected $values = [
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
    ];
}
