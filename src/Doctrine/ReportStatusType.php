<?php

namespace App\Doctrine;

class ReportStatusType extends EnumType
{
    public const string OPEN = 'Open';
    public const string IN_DISCUSSION = 'OnDiscussion';
    public const string CLOSED = 'Closed';

    protected string $name = 'report_status';

    protected array $values = [
        self::OPEN,
        self::IN_DISCUSSION,
        self::CLOSED,
    ];
}
