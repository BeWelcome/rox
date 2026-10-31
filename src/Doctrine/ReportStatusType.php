<?php

namespace App\Doctrine;

use Override;

class ReportStatusType extends EnumType
{
    public const string OPEN = 'Open';
    public const string IN_DISCUSSION = 'OnDiscussion';
    public const string CLOSED = 'Closed';

    #[Override]
    protected string $name = 'report_status';

    #[Override]
    protected array $values = [
        self::OPEN,
        self::IN_DISCUSSION,
        self::CLOSED,
    ];
}
