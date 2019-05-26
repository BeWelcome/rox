<?php

namespace App\Doctrine;

class MemberStatusType extends EnumType
{
    // Possible member statuses
    const AWAITING_MAIL_CONFIRMATION = 'MailToConfirm';
    const PENDING = 'Pending';
    const DUPLICATE_SIGNED = 'DuplicateSigned';
    const NEED_MORE = 'NeedMore';
    const REJECTED = 'Rejected';
    const COMPLETED_PENDING = 'CompletedPending';
    const ACTIVE = 'Active';
    const TAKEN_OUT = 'TakenOut';
    const BANNED = 'Banned';
    const SLEEPER = 'Sleeper';
    const CHOICE_INACTIVE = 'ChoiceInactive';
    const OUT_OF_REMIND = 'OutOfRemind';
    const RENAMED = 'Renamed';
    const ACTIVE_HIDDEN = 'ActiveHidden';
    const SUSPENDED = 'SuspendedBeta';
    const ASKED_TO_LEAVE = 'AskToLeave';
    const STOP_BORING_ME = 'StopBoringMe';
    const PASSED_AWAY = 'PassedAway';
    const BUGGY = 'Buggy';

    const ACTIVE_ALL = "'".
        self::ACTIVE."', '".
        self::ACTIVE_HIDDEN."', '".
        self::CHOICE_INACTIVE."', '".
        self::OUT_OF_REMIND."', '".
        self::PENDING."'";

    const ACTIVE_ALL_ARRAY = [
        self::ACTIVE_HIDDEN,
        self::ACTIVE,
        self::CHOICE_INACTIVE,
        self::OUT_OF_REMIND,
        self::PENDING,
    ];

    const ACTIVE_SEARCH = "'".
        self::ACTIVE."', '".
        self::ACTIVE_HIDDEN."', '".
        self::OUT_OF_REMIND."', '".
        self::PENDING."'";

    const ACTIVE_WITH_MESSAGES = "'".
        self::ACTIVE."', '".
        self::OUT_OF_REMIND."', '".
        self::PENDING."'";

    const MEMBER_COMMENTS = "'".
        self::ACTIVE."', '".
        self::ACTIVE_HIDDEN."', '".
        self::ASKED_TO_LEAVE."', '".
        self::CHOICE_INACTIVE."', '".
        self::OUT_OF_REMIND."', '".
        self::PENDING."'";

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
