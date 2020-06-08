<?php

namespace App\Doctrine;

class ReportStatusType extends EnumType
{
    public const OPEN = 'Open';
    public const IN_DISCUSSION = 'OnDiscussion';
    public const CLOSED = 'Closed';

    /** @var string */
    protected $name = 'report_status';

    /** @var array */
    protected $values = [
        self::OPEN,
        self::IN_DISCUSSION,
        self::CLOSED,
    ];
}
