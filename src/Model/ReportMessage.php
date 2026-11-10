<?php

namespace App\Model;

use DateTimeImmutable;

/**
 * One message of a report to the forum moderators: by the member who reported the post, or by a moderator.
 * The text is HTML (escaped when it was written).
 */
final readonly class ReportMessage
{
    public function __construct(
        public ?DateTimeImmutable $time,
        public ?string $author,
        public string $text,
        public bool $moderator,
    ) {
    }
}
